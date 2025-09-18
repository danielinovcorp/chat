<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create('salas', function (Blueprint $table) {
			$table->id();
			$table->string('avatar_url')->nullable();
			$table->string('nome');
			$table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
			$table->boolean('is_private')->default(false);
			$table->timestamps();
		});
	}
	public function down(): void { Schema::dropIfExists('salas'); }
};
