<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;

// Rota Home — adicionada para corrigir erro de rota não definida
Route::get('/', [UploadController::class, 'index'])->name('pages.home');

// Rotas admin, só para admins autenticados
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::resource('brands', BrandController::class);
    Route::resource('categories', CategoryController::class);

    Route::get('/', [AdminController::class, 'index'])->name('index');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/update-type', [AdminUserController::class, 'updateType'])->name('users.updateType');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');
});

// Rotas públicas para produtos
Route::resource('product', ProductController::class); // público

Route::get('/produtos', [ProductController::class, 'index'])->name('produtos.index'); // público
Route::get('product/{id}', [ProductController::class, 'show'])->name('product.show'); // público

// Uploads públicos
Route::get('/uploads', [UploadController::class, 'allUploads'])->name('uploads.all');
Route::resource('uploads', UploadController::class)->except(['index']);
Route::get('uploads/{id}', [UploadController::class, 'show'])->name('uploads.show');

// Rotas de autenticação para usuário logado
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['verified'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Upload TinyMCE
Route::post('/upload-tinymce-image', [App\Http\Controllers\TinyMCEUploadController::class, 'upload']);

// Arquivo padrão de autenticação
require __DIR__.'/auth.php';
