<?php

namespace App\Http\Controllers;

use App\Models\DmPair;
use App\Models\Sala;
use App\Models\Mensagem;
use Illuminate\Http\Request;

class ChatController extends Controller
{
	public function index(Request $request)
	{
		$user = $request->user();

		// Salas do utilizador
		$salas = $user->salas()->orderBy('nome')->get();

		// DMs do utilizador
		$dmPairs = DmPair::where('user_a_id', $user->id)
			->orWhere('user_b_id', $user->id)
			->get();

		// Seleção atual
		$activeSala = null;
		$activeDmKey = null;
		$mensagens = collect();

		if ($request->filled('sala')) {
			$activeSala = $user->salas()->where('salas.id', $request->integer('sala'))->first();
			abort_if(!$activeSala, 403);

			$mensagens = $activeSala->mensagens()
				->with('autor')->latest()->limit(50)->get()
				->sortBy('id')->values();
		} elseif ($request->filled('dm')) {
			$activeDmKey = $request->query('dm');

			$pair = DmPair::where('dm_key', $activeDmKey)
				->where(function ($q) use ($user) {
					$q->where('user_a_id', $user->id)->orWhere('user_b_id', $user->id);
				})->firstOrFail();

			$mensagens = Mensagem::where('dm_key', $pair->dm_key)
				->with('autor')->latest()->limit(50)->get()
				->sortBy('id')->values();
		}

		// 👉 AQUI: membros da sala ativa (ou vazio se não houver sala)
		$membrosSala = $activeSala
			? $activeSala->membros()->orderBy('name')->get()
			: collect();

		return view('chat.index', [
			'salas'        => $salas,
			'dmPairs'      => $dmPairs,
			'activeSala'   => $activeSala,
			'activeDmKey'  => $activeDmKey,
			'mensagens'    => $mensagens,
			'membrosSala'  => $membrosSala,   // 👉 passa para a view
		]);
	}
}
