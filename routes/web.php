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

Route::get('/', [HomeController::class, 'index'])->name('pages.home');

// Rotas admin, só para admins autenticados
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::resource('brands', BrandController::class);
    Route::resource('categories', CategoryController::class);

    Route::get('/', [AdminController::class, 'index'])->name('index');

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/update-type', [AdminUserController::class, 'updateType'])->name('users.updateType');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

    // Admin uploads index (listagem apenas para admins)
    Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');

    // Também, se quiser usar resource admin para uploads (exceto 'index', que já definimos)
    Route::resource('uploads', UploadController::class)->except(['index']);
});

// Rotas públicas para produtos
Route::resource('product', ProductController::class); // público

Route::get('/produtos', [ProductController::class, 'index'])->name('produtos.index'); // público

// Rotas públicas para uploads (exibir todos uploads, show etc)
Route::get('/uploads', [UploadController::class, 'allUploads'])->name('uploads.all');
// Aqui, caso queira que essas rotas públicas funcionem separadamente do admin (cuidado com conflito de /uploads)
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
Route::post('/upload-tinymce-image', [TinyMCEUploadController::class, 'upload']);

// Arquivo padrão de autenticação
require __DIR__.'/auth.php';
