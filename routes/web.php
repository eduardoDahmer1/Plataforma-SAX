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
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\ImageConvertController; // controlador para conversão

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
    // ALTERAÇÃO AQUI: Rota /admin agora usa ImageUploadController@index para ter $webpImage
    Route::get('/', [ImageUploadController::class, 'index'])->name('index');

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

    // Limpar cache do sistema
    Route::get('/clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');

    // Rotas para upload, delete e formulário da imagem do header
    Route::post('/image-upload', [ImageUploadController::class, 'upload'])->name('image.upload');
    Route::delete('/image-upload', [ImageUploadController::class, 'delete'])->name('image.delete');
    Route::get('/image-upload', [ImageUploadController::class, 'form'])->name('admin.image.form');

    // Rota para conversão de imagens para WebP
    Route::get('/convert-webp', [ImageConvertController::class, 'convertAllToWebp'])->name('convert.webp');
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
