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
use App\Http\Controllers\HomeController;
use App\Http\Controllers\TinyMCEUploadController;
use App\Http\Controllers\Admin\SystemController;

// Rota Home
Route::get('/', [HomeController::class, 'index'])->name('pages.home');

// Rotas públicas para uploads
Route::get('/uploads-publico', [UploadController::class, 'allUploads'])->name('uploads.public');
Route::get('/uploads/{id}', [UploadController::class, 'show'])->name('uploads.show');

// Rotas públicas para produtos (com nomes que batem com seu Blade)
Route::get('/produtos', [ProductController::class, 'index'])->name('produtos.index');
Route::get('/produto/{product}', [ProductController::class, 'show'])->name('produto.show');

// Rotas protegidas para admin
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Painel administrativo
    Route::get('/', [AdminController::class, 'index'])->name('index');

    // Usuários (admin)
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/update-type', [AdminUserController::class, 'updateType'])->name('users.updateType');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Uploads (admin)
    Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');
    Route::resource('uploads', UploadController::class)->except(['index']);

    // Produtos (admin)
    Route::resource('products', ProductController::class);

    // Marcas e Categorias (admin)
    Route::resource('brands', BrandController::class);
    Route::resource('categories', CategoryController::class);

    Route::get('/clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
});

// Autenticação de usuário logado
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->middleware(['verified'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Upload TinyMCE (usado em textarea com editor)
Route::post('/upload-tinymce-image', [TinyMCEUploadController::class, 'upload']);

// Arquivo de autenticação padrão do Laravel
require __DIR__.'/auth.php';
