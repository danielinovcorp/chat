<?php

namespace App\Providers;

use App\Models\Sala;
use App\Policies\SalaPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
	protected $policies = [
		Sala::class => SalaPolicy::class,
	];

	public function boot(): void
	{
		// pra registrar gates extras aqui
	}
}
