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
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\BancardController;
use App\Http\Controllers\CuponUserController;
use App\Http\Controllers\PagoParController;
use App\Http\Controllers\PalaceController;

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
use App\Http\Controllers\Admin\CurrencyControllerAdmin;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\AdminHighlightController;
use App\Http\Controllers\Admin\CuponController;
use App\Http\Controllers\Admin\PalaceAdminController;
use App\Http\Controllers\Admin\ActivateBrandsAndCategoriesController;

// Auth Controllers
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\Auth\UserPreferenceController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/manutencao', fn() => view('manutencao.index'))->name('maintenance.page');
Route::get('/palace', [PalaceController::class, 'index'])->name('palace.index');
// Rota para a página de Bridal (Noivas)
Route::get('/bridal', [App\Http\Controllers\BridalController::class, 'index'])->name('bridal.index');

Route::get('/produtos', [ProductController::class, 'index'])->name('produtos.index');
// Rota principal de detalhes (usando Slug para SEO e navegação de variações)
Route::get('/produto/{id_or_slug}', [ProductController::class, 'show'])->name('produto.show');


// Listagens específicas (Filtros)
Route::get('/categorias/{category}/produtos', [ProductController::class, 'byCategory'])->name('products.byCategory');
Route::get('/subcategorias/{subcategory}/produtos', [ProductController::class, 'bySubcategory'])->name('products.bySubcategory');
Route::get('/childcategorias/{childcategory}/produtos', [ProductController::class, 'byChildcategory'])->name('products.byChildcategory');

// Categorias
Route::get('/categorias', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categorias/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

// Subcategorias
Route::get('/subcategorias', [SubcategoryController::class, 'index'])->name('subcategories.index');
Route::get('/subcategorias/{slug}', [SubcategoryController::class, 'show'])->name('subcategories.show');

// Sub-Subcategorias (Child)
Route::get('/subsubcategorias', [PublicChildcategoryController::class, 'index'])->name('childcategories.index');
Route::get('/subsubcategorias/{slug}', [PublicChildcategoryController::class, 'show'])->name('childcategories.show');

// Marcas
Route::get('/marcas', [BrandController::class, 'publicIndex'])->name('brands.index');
Route::get('/marcas/{slug}', [BrandController::class, 'publicShow'])->name('brands.show');

Route::get('/cart', [CartController::class, 'view'])->name('cart.view');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::match(['post', 'put'], '/cart/update/{productId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');

// Checkout via WhatsApp
Route::get('checkout/whatsapp', [CheckoutController::class, 'whatsapp'])->name('checkout.whatsapp');

// Blog
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');

// Contato
Route::get('/contato', [ContactController::class, 'showForm'])->name('contact.form');
Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');

// Moeda
Route::post('/currency/change', [CurrencyController::class, 'change'])->name('currency.change');

// --- Authenticated User Routes ---
Route::middleware('auth')->group(function () {

        // --- GRUPO BANCARD ---
    // 1. A página que gera o pagamento (A que você acessa no navegador)
    Route::get('/checkout/bancard/{id}', [BancardController::class, 'checkoutPage'])->name('bancard.checkout');

    // 2. A página para onde o usuário volta (GET)
    Route::get('/checkout/bancard/return', [BancardController::class, 'returnPage'])->name('bancard.return');

    // 3. O Webhook que recebe a confirmação (POST)
    // Nota: Removi de dentro do auth se for o Bancard chamando, mas como você definiu bancardCallback, mantenha o nome correto:
    Route::post('/checkout/bancard/callback', [BancardController::class, 'bancardCallback'])->name('bancard.callback');

    // Dashboard e Perfil
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/profile', [UserController::class, 'edit'])->name('user.profile.edit');
    Route::put('/profile', [UserController::class, 'update'])->name('user.profile.update');

    // Rota para exibir os detalhes do produto
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');

    // Pedidos do usuário
    Route::get('/orders', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/orders/{id}', [UserController::class, 'showOrder'])->name('user.orders.show');

    Route::put('admin/orders/{order}', [OrderController::class, 'update'])->name('admin.orders.update');

    Route::get('/meus-preferidos', [UserPreferenceController::class, 'index'])->name('user.preferences');
    Route::post('/user/preferences/toggle', [UserPreferenceController::class, 'toggle'])->name('user.preferences.toggle');

    // Checkout padrão
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success', [UserController::class, 'checkoutSuccess'])->name('checkout.success');

    Route::post('/cart/add-and-checkout', [CartController::class, 'addAndCheckout'])->name('cart.addAndCheckout');

    // Cupons do usuário
    Route::get('cupons', [CuponUserController::class, 'index'])->name('user.cupons');
    Route::post('cupons/remove', [CuponUserController::class, 'remove'])->name('user.cupons.remove');

    Route::post('/user/cupon/apply', [CuponUserController::class, 'applyCupon'])
    ->name('user.applyCupon')
    ->middleware('auth');

    Route::post('/user/cupons/apply', [CuponUserController::class, 'apply'])
    ->name('user.cupons.apply');

    // Bancard
    Route::get('/checkout/bancard/{order}', [BancardController::class, 'checkoutPage'])->name('checkout.bancard');
    Route::post('/checkout/bancard/callback', [BancardController::class, 'bancardCallback'])->name('bancard.callback');

    // Páginas de sucesso e erro
    Route::get('/checkout/success', function () {
        return view('checkout.success');
    })->name('checkout.success');

    Route::get('/checkout/error', function () {
        return view('checkout.error');
    })->name('checkout.error');

    // Checkout com Pagopar
    Route::get('/checkout/pagopar/{order}', [PagoParController::class, 'checkoutPage'])
    ->name('pagopar.checkout');

    // Callback/retorno Pagopar
    Route::post('/checkout/pagopar/callback', [PagoParController::class, 'callback'])
    ->name('pagopar.callback');

    // Depósito
    Route::get('/checkout/deposito/{order}', [CheckoutController::class, 'deposito'])->name('checkout.deposito');
    Route::post('/checkout/deposito/{order}', [CheckoutController::class, 'submitDeposito'])->name('checkout.deposito.submit');

    Route::post('/orders/{order}/deposit', [OrderController::class, 'depositSubmit'])->name('orders.deposit.submit');

    // Excluir conta
    Route::delete('/user/delete', [App\Http\Controllers\Auth\UserController::class, 'destroy'])->name('user.destroy');

    // Email verification
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');
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

    Route::resource('palace', PalaceAdminController::class);

    Route::post('products/{product}/toggle-status', [ProductControllerAdmin::class, 'toggleStatus'])->name('products.toggleStatus');

    // Listar moedas
    Route::get('currencies', [CurrencyControllerAdmin::class, 'index'])->name('currencies.index');

    // Rotas para Ativar/Desativar Marcas e Categorias
    Route::get('activate-control', [ActivateBrandsAndCategoriesController::class, 'index'])->name('activate.index');
    Route::post('activate-toggle/{type}/{id}', [ActivateBrandsAndCategoriesController::class, 'toggleStatus'])->name('activate.toggle');

    Route::get('products/search', [ProductControllerAdmin::class, 'search'])->name('products.search');

    Route::get('sections-home', [AdminHighlightController::class, 'index'])->name('sections_home.index');
    Route::patch('sections-home/update', [AdminHighlightController::class, 'update'])->name('sections_home.update');

    // Adicionar moeda
    Route::post('currencies', [CurrencyControllerAdmin::class, 'store'])->name('currencies.store');

    Route::resource('cupons', CuponController::class);

    Route::get('/produto/{product}', [ProductController::class, 'show'])->name('produto.show');
    Route::get('products/review', [ProductControllerAdmin::class, 'review'])
    ->name('products.review');

    // Editar moeda
    Route::put('currencies/{id}', [CurrencyControllerAdmin::class, 'update'])->name('currencies.update');

    // Definir como padrão
    Route::get('currencies/{id}/default', [CurrencyControllerAdmin::class, 'setDefault'])->name('currencies.default');

    Route::patch('products/{product}/update-highlights', [ProductControllerAdmin::class, 'updateHighlights'])
        ->name('products.updateHighlights');

    Route::get('maintenance', [\App\Http\Controllers\Admin\SystemController::class, 'maintenanceIndex'])->name('maintenance.index');
    Route::get('maintenance/toggle', [\App\Http\Controllers\Admin\SystemController::class, 'toggleMaintenance'])->name('maintenance.toggle');

    // web.php
    Route::get('products/subcategories/{category}', [ProductControllerAdmin::class, 'getSubcategories'])->name('products.getSubcategories');
    Route::get('products/childcategories/{subcategory}', [ProductControllerAdmin::class, 'getChildcategories'])->name('products.getChildcategories');
    Route::delete('childcategories/{childcategory}/delete-photo', [ChildcategoryControllerAdmin::class, 'deletePhoto'])->name('childcategories.deletePhoto');
    Route::delete('childcategories/{childcategory}/delete-banner', [ChildcategoryControllerAdmin::class, 'deleteBanner'])->name('childcategories.deleteBanner');
    Route::resource('childcategories', ChildcategoryControllerAdmin::class)->except(['show']);
    Route::get('childcategorias/{slug}', [ChildcategoryControllerAdmin::class, 'show'])->name('childcategories.show');    

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
    Route::resource('orders', OrderController::class)->only(['index', 'show', 'destroy']);

    // Clients Admin
    Route::resource('clients', ClientController::class)->only(['index', 'show']);

    // Payments
    Route::resource('payments', PaymentMethodController::class);
    Route::post('payments/{id}/toggle-active', [PaymentMethodController::class, 'toggleActive'])->name('payments.toggleActive');

    // Blogs
    Route::resource('blogs', BlogControllerAdmin::class);

    // Upload de imagens via admin
    Route::post('blogs/upload-image', [BlogControllerAdmin::class, 'uploadImage'])->name('blogs.upload-image');

    // Blog Categories
    Route::get('blog-categories', [BlogCategoryController::class, 'index'])->name('blog-categories.index');
    Route::get('blog-categories/create', [BlogCategoryController::class, 'create'])->name('blog-categories.create');
    Route::post('blog-categories', [BlogCategoryController::class, 'store'])->name('blog-categories.store');
    Route::get('blog-categories/{category}/edit', [BlogCategoryController::class, 'edit'])->name('blog-categories.edit');
    Route::get('blog-categories/{category}', [BlogCategoryController::class, 'show'])->name('blog-categories.show');
    Route::put('blog-categories/{category}', [BlogCategoryController::class, 'update'])->name('blog-categories.update');
    Route::delete('blog-categories/{category}', [BlogCategoryController::class, 'destroy'])->name('blog-categories.destroy');

    Route::get('admin/blog-categories/{category}', [BlogCategoryController::class, 'show'])->name('admin.blog-categories.show');

    // Users Admin
    Route::get('users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::post('users/{id}/update-type', [\App\Http\Controllers\Admin\UserController::class, 'updateType'])->name('users.updateType');
    Route::delete('users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

    // Header Image
    Route::post('header/upload', [ImageUploadController::class, 'uploadHeader'])->name('header.upload');
    Route::delete('header/delete', [ImageUploadController::class, 'deleteHeader'])->name('header.delete');

    // Noimage
    Route::post('noimage/upload', [ImageUploadController::class, 'uploadNoimage'])->name('noimage.upload');
    Route::delete('noimage/delete', [ImageUploadController::class, 'deleteNoimage'])->name('noimage.delete');

    // Banner 1
    Route::post('banner1/upload', [ImageUploadController::class, 'uploadBanner1'])->name('banner1.upload');
    Route::delete('banner1/delete', [ImageUploadController::class, 'deleteBanner1'])->name('banner1.delete');

    // Banner 2
    Route::post('banner2/upload', [ImageUploadController::class, 'uploadBanner2'])->name('banner2.upload');
    Route::delete('banner2/delete', [ImageUploadController::class, 'deleteBanner2'])->name('banner2.delete');

    // Banner 3
    Route::post('banner3/upload', [ImageUploadController::class, 'uploadBanner3'])->name('banner3.upload');
    Route::delete('banner3/delete', [ImageUploadController::class, 'deleteBanner3'])->name('banner3.delete');

    // Banner 4
    Route::post('banner4/upload', [ImageUploadController::class, 'uploadBanner4'])->name('banner4.upload');
    Route::delete('banner4/delete', [ImageUploadController::class, 'deleteBanner4'])->name('banner4.delete');

    // Banner 5
    Route::post('banner5/upload', [ImageUploadController::class, 'uploadBanner5'])->name('banner5.upload');
    Route::delete('banner5/delete', [ImageUploadController::class, 'deleteBanner5'])->name('banner5.delete');

    // Banner 6
    Route::post('banner6/upload', [ImageUploadController::class, 'uploadBanner6'])->name('banner6.upload');
    Route::delete('banner6/delete', [ImageUploadController::class, 'deleteBanner6'])->name('banner6.delete');

    // Procure o grupo de rotas do Admin e adicione:
    Route::post('/logopalace/upload', [App\Http\Controllers\ImageUploadController::class, 'uploadLogoPalace'])->name('logopalace.upload');
    Route::delete('logopalace/delete', [App\Http\Controllers\ImageUploadController::class, 'deleteLogoPalace'])->name('logopalace.delete');

    // Banner 7
    Route::post('banner7/upload', [ImageUploadController::class, 'uploadBanner7'])->name('banner7.upload');
    Route::delete('banner7/delete', [ImageUploadController::class, 'deleteBanner7'])->name('banner7.delete');

    // Banner 8
    Route::post('banner8/upload', [ImageUploadController::class, 'uploadBanner8'])->name('banner8.upload');
    Route::delete('banner8/delete', [ImageUploadController::class, 'deleteBanner8'])->name('banner8.delete');

    // Banner 9
    Route::post('banner9/upload', [ImageUploadController::class, 'uploadBanner9'])->name('banner9.upload');
    Route::delete('banner9/delete', [ImageUploadController::class, 'deleteBanner9'])->name('banner9.delete');

    // Banner 10
    Route::post('banner10/upload', [ImageUploadController::class, 'uploadBanner10'])->name('banner10.upload');
    Route::delete('banner10/delete', [ImageUploadController::class, 'deleteBanner10'])->name('banner10.delete');

    // Products Admin
    Route::resource('products', ProductControllerAdmin::class);
    Route::delete('products/{id}/photo', [ProductControllerAdmin::class, 'deletePhoto'])->name('products.deletePhoto');
    Route::delete('products/{id}/gallery/{image}', [ProductControllerAdmin::class, 'deleteGalleryImage'])->name('products.deleteGalleryImage');

    // Brands Admin
    Route::resource('brands', BrandControllerAdmin::class);
    Route::delete('brands/{brand}/delete-logo', [BrandControllerAdmin::class, 'deleteLogo'])->name('brands.deleteLogo');
    Route::delete('brands/{brand}/delete-banner', [BrandControllerAdmin::class, 'deleteBanner'])->name('brands.deleteBanner');
    Route::delete('admin/brands/{brand}/delete-internal-banner', [BrandControllerAdmin::class, 'deleteInternalBanner'])->name('admin.brands.deleteInternalBanner');

    // Contacts Admin
    Route::resource('contatos', ContactControllerAdmin::class)->only(['index', 'destroy']);
    Route::get('contatos/export', [ContactControllerAdmin::class, 'export'])->name('contacts.export');

    // System & Images
    Route::get('clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
    Route::post('image-upload', [ImageUploadController::class, 'upload'])->name('image.upload');
    Route::delete('image-upload', [ImageUploadController::class, 'delete'])->name('image.delete');
    Route::get('image-upload', [ImageUploadController::class, 'form'])->name('image.form');
    Route::post('noimage-upload', [ImageUploadController::class, 'uploadNoimage'])->name('noimage.upload');
    Route::delete('noimage-upload', [ImageUploadController::class, 'deleteNoimage'])->name('noimage.delete');
});

require __DIR__ . '/auth.php';
