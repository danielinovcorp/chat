<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create('mensagem_attachments', function (Blueprint $table) {
			$table->id();
			$table->foreignId('mensagem_id')->constrained('mensagens')->cascadeOnDelete();
			$table->string('path');
			$table->string('mime', 100)->nullable();
			$table->unsignedBigInteger('size')->default(0);
			$table->timestamps();
		});
	}
	public function down(): void { Schema::dropIfExists('mensagem_attachments'); }
};
