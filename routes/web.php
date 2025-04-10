<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;

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

// Rotas administrativas (apenas para admin)
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/update-type', [AdminUserController::class, 'updateType'])->name('users.updateType');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');
});

// Rota de logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rota para upload de imagem TinyMCE
Route::post('/upload-tinymce-image', [App\Http\Controllers\TinyMCEUploadController::class, 'upload']);

// Arquivo de autenticação
require __DIR__.'/auth.php';
