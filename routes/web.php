<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

// Frontend Controllers
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\ChildcategoryController as PublicChildcategoryController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\UploadController;

// Admin Controllers
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\BlogControllerAdmin;
use App\Http\Controllers\Admin\SubcategoryControllerAdmin;
use App\Http\Controllers\Admin\ChildcategoryControllerAdmin;
use App\Http\Controllers\Admin\ContactControllerAdmin;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductControllerAdmin;
use App\Http\Controllers\Admin\CategoryControllerAdmin;
use App\Http\Controllers\Admin\BrandControllerAdmin;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\AdminUserController;

// Auth Controllers
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\User\ProfileController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\ImageConvertController;

// --- Home ---
Route::get('/', [HomeController::class, 'index'])->name('home');

// --- Frontend Public Routes ---
// Subcategories
Route::get('/subcategorias', [SubcategoryController::class, 'index'])->name('subcategories.index');
Route::get('/subcategorias/{slug}', [SubcategoryController::class, 'show'])->name('subcategories.show');

// Childcategories
Route::get('/subsubcategorias', [PublicChildcategoryController::class, 'index'])->name('childcategories.index');
Route::get('/subsubcategorias/{slug}', [PublicChildcategoryController::class, 'show'])->name('childcategories.show');

// Categories
Route::get('/categorias', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categorias/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

// Brands
Route::get('/marcas', [BrandController::class, 'publicIndex'])->name('brands.index');
Route::get('/marcas/{slug}', [BrandController::class, 'publicShow'])->name('brands.show');

// Blogs
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');

// Contact
Route::get('/contato', [ContactController::class, 'showForm'])->name('contact.form');
Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');

// Products
Route::get('/produtos', [ProductController::class, 'index'])->name('produtos.index');
Route::get('/produto/{product}', [ProductController::class, 'show'])->name('produto.show');

// Uploads public
Route::get('/uploads-publico', [UploadController::class, 'allUploads'])->name('uploads.public');
Route::get('/uploads/{id}', [UploadController::class, 'show'])->name('uploads.show');

// Produtos por categoria
Route::get('/categorias/{category}/produtos', [ProductController::class, 'byCategory'])->name('products.byCategory');

// Produtos por subcategoria
Route::get('/subcategorias/{subcategory}/produtos', [ProductController::class, 'bySubcategory'])->name('products.bySubcategory');

// Produtos por childcategory
Route::get('/childcategorias/{childcategory}/produtos', [ProductController::class, 'byChildcategory'])->name('products.byChildcategory');

// Detalhes do produto
Route::get('/produtos/{product}', [ProductController::class, 'show'])->name('products.show');

// Cart
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::get('/cart', [CartController::class, 'view'])->name('cart.view');
Route::match(['post','put'], '/cart/update/{productId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');

// Checkout WhatsApp
Route::get('checkout/whatsapp', [CheckoutController::class, 'whatsapp'])->name('checkout.whatsapp');

// --- Authenticated User Routes ---
Route::middleware('auth')->group(function () {
    // Dashboard e Perfil
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/profile', [UserController::class, 'edit'])->name('user.profile.edit');
    Route::put('/profile', [UserController::class, 'update'])->name('user.profile.update');

    // Pedidos do usuário
    Route::get('/orders', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/orders/{id}', [UserController::class, 'showOrder'])->name('user.orders.show');

    Route::put('admin/orders/{order}', [OrderController::class, 'update'])->name('admin.orders.update');

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success', [UserController::class, 'checkoutSuccess'])->name('checkout.success');

    // Email verification
    Route::get('/email/verify', function () { return view('auth.verify-email'); })->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect('/home');
    })->middleware(['signed'])->name('verification.verify');
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Link de verificação reenviado!');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

// --- Admin Routes ---
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [ImageUploadController::class, 'index'])->name('index');
    Route::get('dashboard', [UserController::class, 'dashboard'])->name('dashboard');

    // web.php
    Route::get('subcategories/{category}', [ProductControllerAdmin::class, 'getSubcategories'])->name('products.getSubcategories');
    Route::get('childcategories/{subcategory}', [ProductControllerAdmin::class, 'getChildcategories'])->name('products.getChildcategories');

    // Childcategories Admin
    Route::delete('childcategories/{childcategory}/delete-photo', [ChildcategoryControllerAdmin::class, 'deletePhoto'])->name('childcategories.deletePhoto');
    Route::delete('childcategories/{childcategory}/delete-banner', [ChildcategoryControllerAdmin::class, 'deleteBanner'])->name('childcategories.deleteBanner');
    Route::resource('childcategories', ChildcategoryControllerAdmin::class);

    // Subcategories Admin
    Route::delete('subcategories/{subcategory}/delete-photo', [SubcategoryControllerAdmin::class, 'deletePhoto'])->name('subcategories.deletePhoto');
    Route::delete('subcategories/{subcategory}/delete-banner', [SubcategoryControllerAdmin::class, 'deleteBanner'])->name('subcategories.deleteBanner');
    Route::resource('subcategories', SubcategoryControllerAdmin::class);

    // Categories Admin
    Route::delete('categories/{category}/delete-photo', [CategoryControllerAdmin::class, 'deletePhoto'])->name('categories.deletePhoto');
    Route::delete('categories/{category}/delete-banner', [CategoryControllerAdmin::class, 'deleteBanner'])->name('categories.deleteBanner');
    Route::get('categories/convert-images', [CategoryControllerAdmin::class, 'convertCategoryImagesToWebp'])->name('categories.convertImages');
    Route::resource('categories', CategoryControllerAdmin::class);

    // Orders Admin
    Route::resource('orders', OrderController::class)->only(['index','show','destroy']);

    // Clients Admin
    Route::resource('clients', ClientController::class)->only(['index','show']);

    // Payments
    Route::resource('payments', PaymentMethodController::class);
    Route::post('payments/{id}/toggle-active', [PaymentMethodController::class, 'toggleActive'])->name('payments.toggleActive');

    // Blogs Admin
    Route::resource('blogs', BlogControllerAdmin::class);
    Route::post('blogs/upload-image', [BlogController::class, 'uploadImage'])->name('blogs.upload-image');

    // Users Admin
    Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::post('users/{id}/update-type', [\App\Http\Controllers\Admin\UserController::class, 'updateType'])->name('users.updateType');
    Route::delete('users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

    // Uploads Admin
    Route::get('uploads', [UploadController::class, 'index'])->name('uploads.index');
    Route::resource('uploads', UploadController::class)->except(['index']);
    Route::post('uploads/trumbowyg-image', [UploadController::class, 'uploadImage'])->name('uploads.trumbowyg-image');

    // Products Admin
    Route::resource('products', ProductControllerAdmin::class);
    Route::delete('products/{id}/photo', [ProductControllerAdmin::class, 'deletePhoto'])->name('products.deletePhoto');
    Route::delete('products/{id}/gallery/{image}', [ProductControllerAdmin::class, 'deleteGalleryImage'])->name('products.deleteGalleryImage');

    // Brands Admin
    Route::resource('brands', BrandControllerAdmin::class);
    Route::delete('brands/{brand}/delete-logo', [BrandControllerAdmin::class, 'deleteLogo'])->name('brands.deleteLogo');
    Route::delete('brands/{brand}/delete-banner', [BrandControllerAdmin::class, 'deleteBanner'])->name('brands.deleteBanner');

    // Contacts Admin
    Route::resource('contatos', ContactControllerAdmin::class)->only(['index','destroy']);
    Route::get('contatos/export', [ContactControllerAdmin::class, 'export'])->name('contacts.export');

    // System & Images
    Route::get('clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
    Route::post('image-upload', [ImageUploadController::class, 'upload'])->name('image.upload');
    Route::delete('image-upload', [ImageUploadController::class, 'delete'])->name('image.delete');
    Route::get('image-upload', [ImageUploadController::class, 'form'])->name('image.form');
    Route::post('noimage-upload', [ImageUploadController::class, 'uploadNoimage'])->name('noimage.upload');
    Route::delete('noimage-upload', [ImageUploadController::class, 'deleteNoimage'])->name('noimage.delete');
    Route::get('convert-webp', [ImageConvertController::class, 'convertAllToWebp'])->name('convert.webp');
});

// --- Auth ---
Auth::routes(['verify' => true]);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

require __DIR__ . '/auth.php';
