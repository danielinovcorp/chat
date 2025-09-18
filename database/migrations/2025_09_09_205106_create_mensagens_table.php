<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create('mensagens', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->foreignId('sala_id')->nullable()->constrained('salas')->cascadeOnDelete();
			$table->string('dm_key')->nullable()->index(); // para DMs
			$table->text('conteudo')->nullable();
			$table->json('metadata')->nullable(); // edited_at, deleted_at, link_preview...
			$table->foreignId('reply_to_message_id')->nullable()->constrained('mensagens')->nullOnDelete();
			$table->timestamps();
		});
	}
	public function down(): void { Schema::dropIfExists('mensagens'); }
};

