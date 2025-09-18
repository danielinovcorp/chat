<?php

namespace App\Events;

use App\Models\Mensagem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcastNow
{
	use SerializesModels;

	public function __construct(public Mensagem $mensagem) {}

	public function broadcastOn(): Channel|array
	{
		return $this->mensagem->sala_id
			? new PresenceChannel("sala.{$this->mensagem->sala_id}")
			: new PrivateChannel("dm.{$this->mensagem->dm_key}");
	}

	public function broadcastAs(): string
	{
		return 'message.sent';
	}

	public function broadcastWith(): array
	{
		return [
			'id' => $this->mensagem->id,
			'user' => [
				'id' => $this->mensagem->autor->id,
				'nome' => $this->mensagem->autor->name,
				'avatar' => $this->mensagem->autor->avatar_url,
			],
			'conteudo' => $this->mensagem->conteudo,
			'created_at' => $this->mensagem->created_at->toIso8601String(),
			'sala_id' => $this->mensagem->sala_id,
			'dm_key' => $this->mensagem->dm_key,
		];
	}
}
