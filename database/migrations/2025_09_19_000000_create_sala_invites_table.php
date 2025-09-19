<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('sala_invites', function (Blueprint $t) {
			$t->id();
			$t->foreignId('sala_id')->constrained('salas')->cascadeOnDelete();
			$t->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
			$t->string('token', 64)->unique();
			$t->unsignedInteger('max_uses')->nullable();      // null = ilimitado
			$t->unsignedInteger('used_count')->default(0);
			$t->timestamp('expires_at')->nullable();           // null = nunca expira
			$t->timestamp('disabled_at')->nullable();          // soft-disable manual
			$t->timestamps();

			$t->index(['sala_id']);
		});
	}
	public function down(): void
	{
		Schema::dropIfExists('sala_invites');
	}
};
