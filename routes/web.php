<?php

// Frontend
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ProductController;               // front controller
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
use App\Http\Controllers\SubcategoryController; // ✅ Adicionado o controller público de subcategorias
use App\Http\Controllers\ChildcategoryController as PublicChildcategoryController;

// Admin
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\BlogControllerAdmin;
use App\Http\Controllers\Admin\SubcategoryControllerAdmin;
use App\Http\Controllers\Admin\ChildcategoryControllerAdmin;
use App\Http\Controllers\Admin\ContactControllerAdmin;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductControllerAdmin;      // admin controller
use App\Http\Controllers\Admin\CategoryControllerAdmin;

// --- Rota Home ---
Route::get('/', [HomeController::class, 'index'])->name('home');

// --- Frontend ---
// Subcategorias públicas
Route::get('/subcategorias', [SubcategoryController::class, 'index'])->name('subcategories.index');
Route::get('/subcategorias/{slug}', [SubcategoryController::class, 'show'])->name('subcategories.show');

// Blogs públicos
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');

// Contato público
Route::get('/contato', [ContactController::class, 'showForm'])->name('contact.form');
Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');

// Categorias públicas
Route::get('/categorias', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categorias/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

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
Route::put('/cart/update/{productId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');

// Sub-subcategorias públicas
Route::get('/subsubcategorias', [PublicChildcategoryController::class, 'index'])->name('childcategories.index');
Route::get('/subsubcategorias/{slug}', [PublicChildcategoryController::class, 'show'])->name('childcategories.show');

// --- Rotas protegidas para admin ---
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {

    Route::get('/', [ImageUploadController::class, 'index'])->name('index');

    // Childcategories admin
    Route::delete('childcategories/{childcategory}/delete-photo', [ChildcategoryControllerAdmin::class, 'deletePhoto'])->name('childcategories.deletePhoto');
    Route::delete('childcategories/{childcategory}/delete-banner', [ChildcategoryControllerAdmin::class, 'deleteBanner'])->name('childcategories.deleteBanner');
    Route::resource('childcategories', ChildcategoryControllerAdmin::class);

    // Subcategories admin
    Route::delete('subcategories/{subcategory}/delete-photo', [SubcategoryControllerAdmin::class, 'deletePhoto'])->name('subcategories.deletePhoto');
    Route::delete('subcategories/{subcategory}/delete-banner', [SubcategoryControllerAdmin::class, 'deleteBanner'])->name('subcategories.deleteBanner');
    Route::delete('subcategories/{subcategory}', [SubcategoryControllerAdmin::class, 'destroy'])->name('subcategories.destroy');
    Route::resource('subcategories', SubcategoryControllerAdmin::class);

    // Categorias admin
    Route::delete('categories/delete-photo/{category}', [CategoryControllerAdmin::class, 'deletePhoto'])->name('categories.deletePhoto');
    Route::delete('categories/delete-banner/{category}', [CategoryControllerAdmin::class, 'deleteBanner'])->name('categories.deleteBanner');
    Route::get('categories/convert-images', [CategoryControllerAdmin::class, 'convertCategoryImagesToWebp'])->name('categories.convertImages');
    Route::resource('categories', CategoryControllerAdmin::class);

    // Pedidos
    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::delete('orders/{id}', [OrderController::class, 'destroy'])->name('orders.destroy');

    // Clientes
    Route::get('clients', [ClientController::class, 'index'])->name('clients.index');
    Route::get('clients/{client}', [ClientController::class, 'show'])->name('clients.show');

    // Métodos de pagamento
    Route::resource('payments', \App\Http\Controllers\Admin\PaymentMethodController::class);
    Route::post('payments/{id}/toggle-active', [\App\Http\Controllers\Admin\PaymentMethodController::class, 'toggleActive'])->name('payments.toggleActive');

    // Blogs admin
    Route::resource('blogs', BlogControllerAdmin::class);
    Route::post('blogs/upload-image', [BlogController::class, 'uploadImage'])->name('blogs.upload-image');

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
    Route::post('uploads/trumbowyg-image', [UploadController::class, 'uploadImage'])->name('uploads.trumbowyg-image');

    // Produtos admin
    Route::resource('products', ProductControllerAdmin::class);

    // Marcas admin
    Route::resource('brands', BrandController::class);

    // Contatos admin
    Route::get('contatos', [ContactControllerAdmin::class, 'index'])->name('contacts.index');
    Route::delete('contatos/{contact}', [ContactControllerAdmin::class, 'destroy'])->name('contacts.destroy');
    Route::get('contacts/export', [ContactControllerAdmin::class, 'export'])->name('contacts.export');

    // Cache e uploads
    Route::get('clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
    Route::post('image-upload', [ImageUploadController::class, 'upload'])->name('image.upload');
    Route::delete('image-upload', [ImageUploadController::class, 'delete'])->name('image.delete');
    Route::get('image-upload', [ImageUploadController::class, 'form'])->name('image.form');
    Route::post('noimage-upload', [ImageUploadController::class, 'uploadNoimage'])->name('noimage.upload');
    Route::delete('noimage-upload', [ImageUploadController::class, 'deleteNoimage'])->name('noimage.delete');
    Route::get('convert-webp', [ImageConvertController::class, 'convertAllToWebp'])->name('convert.webp');
});

// --- Checkout ---
Route::middleware('auth')->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');

    Route::get('/checkout/success', function () {
        return view('checkout.success');
    })->name('checkout.success');
});

// Logout
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

require __DIR__ . '/auth.php';
