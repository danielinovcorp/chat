<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void {
		Schema::create('dm_pairs', function (Blueprint $table) {
			$table->id();
			$table->foreignId('user_a_id')->constrained('users')->cascadeOnDelete();
			$table->foreignId('user_b_id')->constrained('users')->cascadeOnDelete();
			$table->string('dm_key')->unique();
			$table->timestamps();
			$table->unique(['user_a_id','user_b_id']);
		});
	}
	public function down(): void { Schema::dropIfExists('dm_pairs'); }
};
