<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MensagemAttachment extends Model
{
	protected $fillable = ['mensagem_id','path','mime','size'];

	public function mensagem() { return $this->belongsTo(Mensagem::class); }
}
