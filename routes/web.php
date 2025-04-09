<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ProfileController;

// Página inicial - exibe os uploads mais recentes
Route::get('/', [UploadController::class, 'index'])->name('pages.home');

// Página com todos os uploads
Route::get('/uploads', [UploadController::class, 'allUploads'])->name('uploads.index');

// Rotas resource para uploads
Route::resource('uploads', UploadController::class)->except(['index']); // Excluímos o método index do resource

// Rota para visualizar um upload específico
Route::get('uploads/{id}', [UploadController::class, 'show'])->name('uploads.show');

// Rotas de autenticação
Route::middleware('auth')->group(function () {
    // Rota para o dashboard, acessível apenas para usuários autenticados e verificados
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['verified'])->name('dashboard');

    // Rotas para o perfil do usuário
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

require __DIR__.'/auth.php';
