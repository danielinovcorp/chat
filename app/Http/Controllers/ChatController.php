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

		// Mapa de "dm_key" => outro usuário (com avatar) para exibir na sidebar
		$dmUsers = collect();
		if ($dmPairs->isNotEmpty()) {
			$otherIds = $dmPairs->map(
				fn($p) =>
				$p->user_a_id === $user->id ? $p->user_b_id : $p->user_a_id
			)->unique()->values();

			$usersById = \App\Models\User::whereIn('id', $otherIds)->get()->keyBy('id');

			$dmUsers = $dmPairs->mapWithKeys(function ($p) use ($user, $usersById) {
				$otherId = $p->user_a_id === $user->id ? $p->user_b_id : $p->user_a_id;
				return [$p->dm_key => $usersById->get($otherId)];
			});
		}

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
			'dmUsers'      => $dmUsers,
			'activeSala'   => $activeSala,
			'activeDmKey'  => $activeDmKey,
			'mensagens'    => $mensagens,
			'membrosSala'  => $membrosSala,   // 👉 passa para a view
		]);
	}
}
