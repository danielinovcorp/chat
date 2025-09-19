<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
	/** @use HasFactory<\Database\Factories\UserFactory> */
	use HasFactory, Notifiable;

	/**
	 * Campos atribuíveis em massa.
	 */
	protected $fillable = [
		'name',
		'email',
		'password',
		'avatar_path', // <-- novo
	];

	/**
	 * Campos ocultos.
	 */
	protected $hidden = [
		'password',
		'remember_token',
	];

	/**
	 * Casts.
	 */
	protected function casts(): array
	{
		return [
			'email_verified_at' => 'datetime',
			'password' => 'hashed',
		];
	}

	/**
	 * URL pública do avatar (derivada de avatar_path; fallback para Gravatar).
	 */
	public function getAvatarUrlAttribute(): string
	{
		if (!empty($this->avatar_path)) {
			return Storage::disk('public')->url($this->avatar_path);
		}

		return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim((string) $this->email))) . '?s=240&d=mp';
	}

	// Relacionamentos
	public function salas()
	{
		return $this->belongsToMany(\App\Models\Sala::class, 'sala_utilizador', 'utilizador_id', 'sala_id')
			->withPivot(['papel', 'notificacoes_muted', 'joined_at']);
	}

	public function mensagens()
	{
		return $this->hasMany(\App\Models\Mensagem::class);
	}
}
