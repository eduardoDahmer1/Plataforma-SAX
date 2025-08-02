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
use App\Http\Controllers\ImageConvertController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\ChildcategoryController;

use App\Http\Controllers\ContactController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;

// Rota Home
Route::get('/', [HomeController::class, 'index'])->name('home');

// Frontend
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');

// Página de contato pública
Route::get('/contato', [ContactController::class, 'showForm'])->name('contact.form');
Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');

// Frontend - Categorias
Route::get('/categorias', [\App\Http\Controllers\CategoryController::class, 'publicIndex'])->name('categories.index');
Route::get('/categorias/{category}', [\App\Http\Controllers\CategoryController::class, 'publicShow'])->name('categories.show');

// Frontend - Marcas
Route::get('/marcas', [\App\Http\Controllers\BrandController::class, 'publicIndex'])->name('brands.index');
Route::get('/marcas/{brand}', [\App\Http\Controllers\BrandController::class, 'publicShow'])->name('brands.show');

// Rotas públicas para uploads
Route::get('/uploads-publico', [UploadController::class, 'allUploads'])->name('uploads.public');
Route::get('/uploads/{id}', [UploadController::class, 'show'])->name('uploads.show');

// Rotas públicas para produtos
Route::get('/produtos', [ProductController::class, 'index'])->name('produtos.index');
Route::get('/produto/{product}', [ProductController::class, 'show'])->name('produto.show');

// Rotas protegidas para admin
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [ImageUploadController::class, 'index'])->name('index');

    Route::resource('blogs', AdminBlogController::class);

    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/update-type', [AdminUserController::class, 'updateType'])->name('users.updateType');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::get('users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::post('users/{id}/update-type', [\App\Http\Controllers\Admin\UserController::class, 'updateType'])->name('users.updateType');
    Route::delete('users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

    Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');
    Route::resource('uploads', UploadController::class)->except(['index']);

    Route::post('/blogs/upload-image', [BlogController::class, 'uploadImage'])->name('blogs.upload-image');
    Route::post('/uploads/trumbowyg-image', [UploadController::class, 'uploadImage'])->name('uploads.trumbowyg-image');

    Route::resource('products', ProductController::class);

    Route::resource('subcategories', SubcategoryController::class);
    Route::resource('childcategories', ChildcategoryController::class);

    Route::get('/contatos', [AdminContactController::class, 'index'])->name('contacts.index');

    Route::resource('brands', BrandController::class);
    Route::resource('categories', CategoryController::class);

    Route::get('/clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');

    Route::post('/image-upload', [ImageUploadController::class, 'upload'])->name('image.upload');
    Route::delete('/image-upload', [ImageUploadController::class, 'delete'])->name('image.delete');
    Route::get('/image-upload', [ImageUploadController::class, 'form'])->name('image.form');

    Route::post('/noimage-upload', [ImageUploadController::class, 'uploadNoimage'])->name('noimage.upload');
    Route::delete('/noimage-upload', [ImageUploadController::class, 'deleteNoimage'])->name('noimage.delete');

    Route::get('/convert-webp', [ImageConvertController::class, 'convertAllToWebp'])->name('convert.webp');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->middleware(['verified'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

require __DIR__.'/auth.php';
