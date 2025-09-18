<?php

namespace App\Http\Controllers;

use App\Models\Mensagem;
use App\Models\Sala;
use App\Models\User;
use App\Models\DmPair;
use Illuminate\Http\Request;
use App\Events\MessageSent;

class MessageController extends Controller
{
	public function storeSala(Request $request, Sala $sala)
	{
		$user = $request->user();
		abort_unless($user->salas()->where('salas.id', $sala->id)->exists(), 403);

		$data = $request->validate(['conteudo' => 'required|string|max:5000']);

		$msg = Mensagem::create([
			'user_id'  => $user->id,
			'sala_id'  => $sala->id,
			'conteudo' => $data['conteudo'],
		]);

		$msg->load('autor');
		broadcast(new MessageSent($msg))->toOthers();

		if ($request->wantsJson()) {
			return response()->json([
				'id'         => $msg->id,
				'user'       => ['id' => $msg->autor->id, 'nome' => $msg->autor->name, 'avatar' => $msg->autor->avatar_url],
				'conteudo'   => $msg->conteudo,
				'created_at' => $msg->created_at->toIso8601String(),
				'sala_id'    => $msg->sala_id,
				'dm_key'     => $msg->dm_key,
			]);
		}

		return redirect()->route('chat.index', ['sala' => $sala->id]);
	}

	public function storeDm(Request $request, User $user)
	{
		$auth = $request->user();
		abort_if($auth->id === $user->id, 422, 'DM consigo mesmo não');

		$key  = DmPair::keyFor($auth->id, $user->id);
		$pair = DmPair::firstOrCreate(
			['user_a_id' => min($auth->id, $user->id), 'user_b_id' => max($auth->id, $user->id)],
			['dm_key' => $key]
		);

		$data = $request->validate(['conteudo' => 'required|string|max:5000']);

		$msg = Mensagem::create([
			'user_id'  => $auth->id,
			'dm_key'   => $pair->dm_key,
			'conteudo' => $data['conteudo'],
		]);

		$msg->load('autor');
		broadcast(new MessageSent($msg))->toOthers();

		if ($request->wantsJson()) {
			return response()->json([
				'id'         => $msg->id,
				'user'       => ['id' => $msg->autor->id, 'nome' => $msg->autor->name, 'avatar' => $msg->autor->avatar_url],
				'conteudo'   => $msg->conteudo,
				'created_at' => $msg->created_at->toIso8601String(),
				'sala_id'    => $msg->sala_id,
				'dm_key'     => $msg->dm_key,
			]);
		}

		return redirect()->route('chat.index', ['dm' => $pair->dm_key]);
	}

	public function openDm(Request $request, User $user)
	{
		$auth = $request->user();

		abort_if($auth->id === $user->id, 422, 'DM consigo mesmo não');

		// precisam compartilhar ao menos UMA sala
		$compartilhamSala = $auth->salas()
			->whereHas('membros', fn($q) => $q->where('users.id', $user->id))
			->exists();

		abort_unless($compartilhamSala, 403, 'Só podes enviar DM a membros de uma das tuas salas.');

		$key  = DmPair::keyFor($auth->id, $user->id);
		$pair = DmPair::firstOrCreate(
			['user_a_id' => min($auth->id, $user->id), 'user_b_id' => max($auth->id, $user->id)],
			['dm_key' => $key]
		);

		return redirect()->route('chat.index', ['dm' => $pair->dm_key]);
	}
}
