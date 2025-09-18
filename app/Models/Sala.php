<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
	protected $fillable = ['avatar_url','nome','created_by','is_private'];

	public function membros() {
		return $this->belongsToMany(User::class, 'sala_utilizador', 'sala_id', 'utilizador_id')
			->withPivot(['papel','notificacoes_muted','joined_at']);
	}

	public function mensagens() {
		return $this->hasMany(Mensagem::class);
	}

	public function criador() {
		return $this->belongsTo(User::class, 'created_by');
	}
}
