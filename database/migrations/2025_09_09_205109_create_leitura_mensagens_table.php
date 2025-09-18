<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create('leitura_mensagens', function (Blueprint $table) {
			$table->id();
			$table->foreignId('mensagem_id')->constrained('mensagens')->cascadeOnDelete();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->timestamp('lida_em')->useCurrent();
			$table->unique(['mensagem_id','user_id']);
		});
	}
	public function down(): void { Schema::dropIfExists('leitura_mensagens'); }
};
