<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensagem extends Model
{
	protected $table = 'mensagens';
	protected $fillable = ['user_id','sala_id','dm_key','conteudo','metadata','reply_to_message_id'];
	protected $casts = ['metadata' => 'array'];

	public function autor() { return $this->belongsTo(User::class, 'user_id'); }
	public function sala()  { return $this->belongsTo(Sala::class); }
	public function replyTo(){ return $this->belongsTo(Mensagem::class, 'reply_to_message_id'); }
	public function anexos(){ return $this->hasMany(MensagemAttachment::class); }
}
