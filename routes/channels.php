<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\DmPair;

// (opcional) canal gerado pelo Breeze/laravel para notificações privadas por user
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
	return (int) $user->id === (int) $id;
});

// PRESENCE: sala.{salaId}  -> usado com Echo.join('sala.{id}')
Broadcast::channel('sala.{salaId}', function ($user, $salaId) {
	$isMember = $user->salas()->where('salas.id', $salaId)->exists();
	if (! $isMember) {
		return false;
	}

	// presence channel precisa devolver os dados do utilizador
	return [
		'id'     => $user->id,
		'name'   => $user->name,
		'avatar' => $user->avatar_url,
	];
});

// PRIVATE: dm.{dmKey}  -> usado com Echo.private('dm.{dmKey}')
Broadcast::channel('dm.{dmKey}', function ($user, $dmKey) {
	return DmPair::where('dm_key', $dmKey)
		->where(function ($q) use ($user) {
			$q->where('user_a_id', $user->id)
				->orWhere('user_b_id', $user->id);
		})
		->exists();
});
