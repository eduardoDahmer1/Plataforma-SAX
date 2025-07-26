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
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\ImageConvertController; // controlador para conversão
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\ChildcategoryController;

// Controllers para contato, com alias para o admin
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;

// Rota Home
Route::get('/', [HomeController::class, 'index'])->name('pages.home');

// Frontend
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');

// Página de contato pública
Route::get('/contato', [ContactController::class, 'showForm'])->name('contact.form');
Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');

// Rotas públicas para uploads
Route::get('/uploads-publico', [UploadController::class, 'allUploads'])->name('uploads.public');
Route::get('/uploads/{id}', [UploadController::class, 'show'])->name('uploads.show');

// Rotas públicas para produtos
Route::get('/produtos', [ProductController::class, 'index'])->name('produtos.index');
Route::get('/produto/{product}', [ProductController::class, 'show'])->name('produto.show');

// Rotas protegidas para admin
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    // Dashboard admin
    Route::get('/', [ImageUploadController::class, 'index'])->name('index');

    Route::resource('blogs', AdminBlogController::class);

    // Usuários (admin)
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/update-type', [AdminUserController::class, 'updateType'])->name('users.updateType');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::get('users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::post('users/{id}/update-type', [\App\Http\Controllers\Admin\UserController::class, 'updateType'])->name('users.updateType');
    Route::delete('users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

    // Uploads (admin)
    Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');
    Route::resource('uploads', UploadController::class)->except(['index']);

    // Produtos (admin)
    Route::resource('products', ProductController::class);

    Route::resource('subcategories', SubcategoryController::class);
    Route::resource('childcategories', ChildcategoryController::class);

    // Contatos (admin)
    Route::get('/contatos', [AdminContactController::class, 'index'])->name('contacts.index');

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

// Arquivo de autenticação padrão do Laravel
require __DIR__.'/auth.php';
