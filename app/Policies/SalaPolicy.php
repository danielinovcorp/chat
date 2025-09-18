<?php

namespace App\Policies;

use App\Models\Sala;
use App\Models\User;

class SalaPolicy
{
	public function create(User $user): bool
	{
		return $user->permissao === 'admin';
	}

	public function update(User $user, Sala $sala): bool
	{
		return $user->permissao === 'admin' || $sala->created_by === $user->id;
	}

	public function delete(User $user, Sala $sala): bool
	{
		return $user->permissao === 'admin' || $sala->created_by === $user->id;
	}

	// (opcional) ver/gerir membros no futuro
	public function manageMembers(User $user, Sala $sala): bool
	{
		if ($user->permissao === 'admin' || $sala->created_by === $user->id) return true;
		// moderadores pela pivot, se usar
		return $user->salas()
			->where('salas.id', $sala->id)
			->wherePivot('papel', 'moderator')
			->exists();
	}
}
