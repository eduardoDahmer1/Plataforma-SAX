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
use App\Http\Controllers\CartController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CheckoutController;


// admin
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\SubcategoryController;
use App\Http\Controllers\Admin\ChildcategoryController;
use App\Http\Controllers\Admin\ContactController as AdminContactController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\OrderController;


// --- Rota Home ---
Route::get('/', [HomeController::class, 'index'])->name('home');

// --- Frontend ---
// Blogs
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');

// Contato público
Route::get('/contato', [ContactController::class, 'showForm'])->name('contact.form');
Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');

// Categorias públicas
Route::get('/categorias', [CategoryController::class, 'publicIndex'])->name('categories.index');
Route::get('/categorias/{category}', [CategoryController::class, 'publicShow'])->name('categories.show');

// Marcas públicas
Route::get('/marcas', [BrandController::class, 'publicIndex'])->name('brands.index');
Route::get('/marcas/{brand}', [BrandController::class, 'publicShow'])->name('brands.show');

// Uploads públicos
Route::get('/uploads-publico', [UploadController::class, 'allUploads'])->name('uploads.public');
Route::get('/uploads/{id}', [UploadController::class, 'show'])->name('uploads.show');

// Produtos públicos
Route::get('/produtos', [ProductController::class, 'index'])->name('produtos.index');
Route::get('/produto/{product}', [ProductController::class, 'show'])->name('produto.show');

// Carrinho
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::get('/cart', [CartController::class, 'view'])->name('cart.view');
Route::post('/cart/update/{productId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');

// --- Rotas protegidas para admin ---
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {

    Route::get('/', [ImageUploadController::class, 'index'])->name('index');

    // Pedidos
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    // Clientes
    Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');

    Route::resource('payments', \App\Http\Controllers\Admin\PaymentMethodController::class);
    Route::post('payments/{id}/toggle-active', [\App\Http\Controllers\Admin\PaymentMethodController::class, 'toggleActive'])->name('payments.toggleActive');

    // Pedidos
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');

    Route::delete('orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');

    // Clientes
    Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');

    // Blogs admin
    Route::resource('blogs', AdminBlogController::class);

    // Usuários admin
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::post('users/{user}/update-type', [AdminUserController::class, 'updateType'])->name('users.updateType');
    Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    Route::get('users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::post('users/{id}/update-type', [\App\Http\Controllers\Admin\UserController::class, 'updateType'])->name('users.updateType');
    Route::delete('users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

    // Uploads admin
    Route::get('uploads', [UploadController::class, 'index'])->name('uploads.index');
    Route::resource('uploads', UploadController::class)->except(['index']);

    // Uploads de imagens específicas
    Route::post('blogs/upload-image', [BlogController::class, 'uploadImage'])->name('blogs.upload-image');
    Route::post('uploads/trumbowyg-image', [UploadController::class, 'uploadImage'])->name('uploads.trumbowyg-image');

    // Produtos e categorias admin
    Route::resource('products', ProductController::class);
    Route::resource('subcategories', SubcategoryController::class);
    Route::resource('childcategories', ChildcategoryController::class);

    // Contatos admin
    Route::get('contatos', [AdminContactController::class, 'index'])->name('contacts.index');
    Route::delete('contatos/{contact}', [AdminContactController::class, 'destroy'])->name('contacts.destroy');
    Route::get('contacts/export', [AdminContactController::class, 'export'])->name('contacts.export');

    // Brands e categories admin
    Route::resource('brands', BrandController::class);
    Route::resource('categories', CategoryController::class);

    // Cache, uploads e conversões
    Route::get('clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
    Route::post('image-upload', [ImageUploadController::class, 'upload'])->name('image.upload');
    Route::delete('image-upload', [ImageUploadController::class, 'delete'])->name('image.delete');
    Route::get('image-upload', [ImageUploadController::class, 'form'])->name('image.form');
    Route::post('noimage-upload', [ImageUploadController::class, 'uploadNoimage'])->name('noimage.upload');
    Route::delete('noimage-upload', [ImageUploadController::class, 'deleteNoimage'])->name('noimage.delete');
    Route::get('convert-webp', [ImageConvertController::class, 'convertAllToWebp'])->name('convert.webp');
});

// Rotas autenticadas
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->middleware(['verified'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Checkout com middleware 'cliente'
    Route::middleware('cliente')->group(function () {
        Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
        Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

        Route::get('/checkout/1', [CheckoutController::class, 'step1'])->name('checkout.step1');
        Route::post('/checkout/1', [CheckoutController::class, 'storeStep1']);
        Route::get('/checkout/2', [CheckoutController::class, 'step2'])->name('checkout.step2');
        Route::post('/checkout/2', [CheckoutController::class, 'storeStep2']);
        Route::get('/checkout/3', [CheckoutController::class, 'step3'])->name('checkout.step3');
        Route::post('/checkout/3', [CheckoutController::class, 'storeStep3']);
        Route::get('/checkout/4', [CheckoutController::class, 'step4'])->name('checkout.step4');
        Route::post('/checkout/finish', [CheckoutController::class, 'finish'])->name('checkout.finish');
    });
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

require __DIR__.'/auth.php';
