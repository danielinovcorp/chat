<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::table('users', function (Blueprint $table) {
			$table->string('avatar_url')->nullable();
			$table->string('permissao')->default('user');   // admin | user
			$table->string('estado')->default('ativo');     // ativo | inativo | pendente
		});
	}
	public function down(): void {
		Schema::table('users', function (Blueprint $table) {
			$table->dropColumn(['avatar_url','permissao','estado']);
		});
	}
};

