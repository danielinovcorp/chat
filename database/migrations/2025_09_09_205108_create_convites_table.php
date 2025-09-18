<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create('convites', function (Blueprint $table) {
			$table->id();
			$table->foreignId('sala_id')->constrained('salas')->cascadeOnDelete();
			$table->string('convidado_email')->nullable();
			$table->foreignId('convidado_user_id')->nullable()->constrained('users')->nullOnDelete();
			$table->string('token')->unique();
			$table->timestamp('expires_at')->nullable();
			$table->string('status')->default('pendente'); // pendente | aceite | revogado
			$table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
			$table->timestamps();
		});
	}
	public function down(): void { Schema::dropIfExists('convites'); }
};
