<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Sala;

class DevSeeder extends Seeder
{
	public function run(): void
	{
		$admin = User::firstOrCreate(
			['email' => 'admin@example.com'],
			['name' => 'Admin', 'password' => bcrypt('password'), 'permissao' => 'admin', 'estado' => 'ativo']
		);

		$ana = User::firstOrCreate(
			['email' => 'ana@example.com'],
			['name' => 'Ana', 'password' => bcrypt('password')]
		);

		$bruno = User::firstOrCreate(
			['email' => 'bruno@example.com'],
			['name' => 'Bruno', 'password' => bcrypt('password')]
		);

		$sala = Sala::firstOrCreate(
			['nome' => 'Geral', 'created_by' => $admin->id],
			['is_private' => false]
		);

		$sala->membros()->syncWithoutDetaching([
			$admin->id => ['papel' => 'owner'],
			$ana->id   => ['papel' => 'member'],
			$bruno->id => ['papel' => 'member'],
		]);
	}
}
