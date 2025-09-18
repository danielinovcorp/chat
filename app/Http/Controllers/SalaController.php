<?php

namespace App\Http\Controllers;

use App\Models\Sala;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SalaController extends Controller
{
	public function create()
	{
		$this->authorize('create', Sala::class);
		return view('salas.create');
	}

	public function store(Request $request)
	{
		$this->authorize('create', Sala::class);

		$data = $request->validate([
			'nome'       => 'required|string|max:100',
			'avatar'     => 'nullable|image|max:2048',
			'is_private' => 'sometimes|boolean',
		]);

		$avatarUrl = null;
		if ($request->hasFile('avatar')) {
			$path = $request->file('avatar')->store('avatars/salas', 'public');
			$avatarUrl = Storage::disk('public')->url($path);
		}

		$sala = Sala::create([
			'nome'        => $data['nome'],
			'avatar_url'  => $avatarUrl,
			'created_by'  => $request->user()->id,
			'is_private'  => $request->boolean('is_private'),
		]);

		// criador vira owner da sala
		$sala->membros()->attach($request->user()->id, ['papel' => 'owner']);

		return redirect()->route('chat.index', ['sala' => $sala->id])
			->with('status', 'Sala criada com sucesso.');
	}

	public function edit(Sala $sala)
	{
		$this->authorize('update', $sala);
		return view('salas.edit', compact('sala'));
	}

	public function update(Request $request, Sala $sala)
	{
		$this->authorize('update', $sala);

		$data = $request->validate([
			'nome'       => 'required|string|max:100',
			'avatar'     => 'nullable|image|max:2048',
			'is_private' => 'sometimes|boolean',
		]);

		if ($request->hasFile('avatar')) {
			$path = $request->file('avatar')->store('avatars/salas', 'public');
			$sala->avatar_url = Storage::disk('public')->url($path);
		}

		$sala->nome = $data['nome'];
		$sala->is_private = $request->boolean('is_private');
		$sala->save();

		return redirect()->route('chat.index', ['sala' => $sala->id])
			->with('status', 'Sala atualizada.');
	}

	public function destroy(Sala $sala)
	{
		$this->authorize('delete', $sala);
		$sala->delete();

		return redirect()->route('chat.index')->with('status', 'Sala apagada.');
	}
}
