<?php

// front
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
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\ImageConvertController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;

// admin
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\ChildcategoryController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;

// --- Rota Home ---
Route::get('/', [HomeController::class, 'index'])->name('home');

// --- Frontend ---
// Listagem de blogs
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
// Detalhe de um blog específico
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');

// --- Página de contato pública ---
// Formulário de contato público
Route::get('/contato', [ContactController::class, 'showForm'])->name('contact.form');
// Envio do formulário de contato público
Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');

// --- Frontend - Categorias ---
// Listagem pública de categorias
Route::get('/categorias', [CategoryController::class, 'publicIndex'])->name('categories.index');
// Exibir categoria específica
Route::get('/categorias/{category}', [CategoryController::class, 'publicShow'])->name('categories.show');

// --- Frontend - Marcas ---
// Listagem pública de marcas
Route::get('/marcas', [BrandController::class, 'publicIndex'])->name('brands.index');
// Exibir marca específica
Route::get('/marcas/{brand}', [BrandController::class, 'publicShow'])->name('brands.show');

// --- Rotas públicas para uploads ---
Route::get('/uploads-publico', [UploadController::class, 'allUploads'])->name('uploads.public');
Route::get('/uploads/{id}', [UploadController::class, 'show'])->name('uploads.show');

// --- Rotas públicas para produtos ---
Route::get('/produtos', [ProductController::class, 'index'])->name('produtos.index');
Route::get('/produto/{product}', [ProductController::class, 'show'])->name('produto.show');

// --- Rotas protegidas para admin ---
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {

    // Dashboard admin
    Route::get('/', [ImageUploadController::class, 'index'])->name('index');

    // Gerenciamento de blogs no admin
    Route::resource('blogs', AdminBlogController::class);

    // Gerenciamento de usuários no admin
    Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('/users/{user}/update-type', [AdminUserController::class, 'updateType'])->name('users.updateType');
    Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::get('users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::post('users/{id}/update-type', [\App\Http\Controllers\Admin\UserController::class, 'updateType'])->name('users.updateType');
    Route::delete('users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

    // Gerenciamento de uploads no admin
    Route::get('/uploads', [UploadController::class, 'index'])->name('uploads.index');
    Route::resource('uploads', UploadController::class)->except(['index']);

    // Uploads de imagens para blogs e editor trumbowyg
    Route::post('/blogs/upload-image', [BlogController::class, 'uploadImage'])->name('blogs.upload-image');
    Route::post('/uploads/trumbowyg-image', [UploadController::class, 'uploadImage'])->name('uploads.trumbowyg-image');

    // Gerenciamento de produtos, categorias e marcas no admin
    Route::resource('products', ProductController::class);
    Route::resource('subcategories', SubcategoryController::class);
    Route::resource('childcategories', ChildcategoryController::class);

    // --- Rotas admin para contatos ---
    // Listar contatos recebidos
    Route::get('/contatos', [AdminContactController::class, 'index'])->name('contacts.index');
    // Excluir contato específico
    Route::delete('/contatos/{contact}', [AdminContactController::class, 'destroy'])->name('contacts.destroy');

    Route::get('contacts/export', [AdminContactController::class, 'export'])->name('contacts.export');

    Route::resource('brands', BrandController::class);
    Route::resource('categories', CategoryController::class);

    // Sistema: limpar cache
    Route::get('/clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');

    // Uploads e manipulação de imagens no admin
    Route::post('/image-upload', [ImageUploadController::class, 'upload'])->name('image.upload');
    Route::delete('/image-upload', [ImageUploadController::class, 'delete'])->name('image.delete');
    Route::get('/image-upload', [ImageUploadController::class, 'form'])->name('image.form');

    Route::post('/noimage-upload', [ImageUploadController::class, 'uploadNoimage'])->name('noimage.upload');
    Route::delete('/noimage-upload', [ImageUploadController::class, 'deleteNoimage'])->name('noimage.delete');

    // Converter imagens para webp
    Route::get('/convert-webp', [ImageConvertController::class, 'convertAllToWebp'])->name('convert.webp');
});

// Rotas autenticadas para perfil e dashboard
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->middleware(['verified'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

require __DIR__.'/auth.php';
