<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\SalaController;
use App\Http\Controllers\SalaInviteController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('/salas/{sala}/mensagens', [MessageController::class, 'storeSala'])->name('mensagens.sala.store');
    Route::post('/dm/{user}', [MessageController::class, 'storeDm'])->name('mensagens.dm.store');
});

require __DIR__.'/auth.php';


Route::middleware(['auth'])->group(function () {
    Route::resource('salas', SalaController::class)->only(['create','store','edit','update','destroy']);
});

Route::middleware(['auth'])->group(function () {
    // abrir a DM (gera o par se não existir) e redireciona pro chat
    Route::get('/dm/with/{user}', [MessageController::class, 'openDm'])
        ->name('dm.open');

    // enviar mensagem na DM (você já tem)
    Route::post('/dm/{user}', [MessageController::class, 'storeDm'])
        ->name('mensagens.dm.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/salas/{sala}/invites', [SalaInviteController::class, 'store'])->name('salas.invites.store');
    Route::delete('/invites/{invite}',   [SalaInviteController::class, 'disable'])->name('salas.invites.disable');
});

// link público (pode exigir login no meio do caminho)
Route::get('/i/{token}', [SalaInviteController::class, 'accept'])->name('salas.invites.accept');
