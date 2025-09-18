<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create('sala_utilizador', function (Blueprint $table) {
			$table->id();
			$table->foreignId('sala_id')->constrained('salas')->cascadeOnDelete();
			$table->foreignId('utilizador_id')->constrained('users')->cascadeOnDelete();
			$table->string('papel')->default('member'); // owner | moderator | member
			$table->boolean('notificacoes_muted')->default(false);
			$table->timestamp('joined_at')->useCurrent();
			$table->unique(['sala_id','utilizador_id']);
		});
	}
	public function down(): void { Schema::dropIfExists('sala_utilizador'); }
};
