<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

// Frontend Controllers
use App\Http\Controllers\AllCategoriesController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SubcategoryController;
use App\Http\Controllers\CategoriasFilhasController as PublicCategoriasFilhasController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CuponUserController;
use App\Http\Controllers\PagoParController;
use App\Http\Controllers\PalaceController;
use App\Http\Controllers\BridalController;

// Admin Controllers
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\BlogControllerAdmin;
use App\Http\Controllers\Admin\SubcategoryControllerAdmin;
use App\Http\Controllers\Admin\CategoriasFilhasControllerAdmin;
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
use App\Http\Controllers\Admin\BridalAdminController;
use App\Http\Controllers\Admin\ActivateBrandsAndCategoriesController;

// Auth Controllers
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\Auth\UserPreferenceController;

// --- Public Routes ---
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/manutencao', fn() => view('manutencao.index'))->name('maintenance.page');
Route::get('/palace', [PalaceController::class, 'index'])->name('palace.index');
Route::get('/bridal', [BridalController::class, 'index'])->name('bridal.index');
Route::get('/bistro', fn() => view('cafe_bistro.index'))->name('cafe_bistro');

Route::get('/categorias-gerais', [App\Http\Controllers\AllCategoriesController::class, 'index'])->name('all-categories.index');

Route::get('/produtos', [ProductController::class, 'index'])->name('produtos.index');
Route::get('/produto/{id_or_slug}', [ProductController::class, 'show'])->name('produto.show');

// Listagens específicas (Filtros)
Route::get('/categorias/{category}/produtos', [ProductController::class, 'byCategory'])->name('products.byCategory');
Route::get('/subcategorias/{subcategory}/produtos', [ProductController::class, 'bySubcategory'])->name('products.bySubcategory');
Route::get('/categorias-filhas/{categoriasfilhas}/produtos', [ProductController::class, 'byCategoriasFilhas'])->name('products.byCategoriasFilhas');

// Categorias
Route::get('/categorias', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categorias/{category:slug}', [CategoryController::class, 'show'])->name('categories.show');

// Subcategorias
Route::get('/subcategorias', [SubcategoryController::class, 'index'])->name('subcategories.index');
Route::get('/subcategorias/{slug}', [SubcategoryController::class, 'show'])->name('subcategories.show');

// Sub-Subcategorias (Child)
Route::get('/categorias-filhas', [PublicCategoriasFilhasController::class, 'index'])->name('categorias-filhas.index');
Route::get('/categorias-filhas/{slug}', [PublicCategoriasFilhasController::class, 'show'])->name('categorias-filhas.show');

// Marcas
Route::get('/marcas', [BrandController::class, 'publicIndex'])->name('brands.index');
Route::get('/marcas/{slug}', [BrandController::class, 'publicShow'])->name('brands.show');

// Cart
Route::get('/cart', [CartController::class, 'view'])->name('cart.view');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::match(['post', 'put'], '/cart/update/{productId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');

// Blog
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');

// Contato
Route::get('/contato', [ContactController::class, 'showForm'])->name('contact.form');
Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');

Route::post('/currency/change', [CurrencyController::class, 'change'])->name('currency.change');

// --- Authenticated User Routes ---
Route::middleware('auth')->group(function () {

    // Dashboard e Perfil
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/profile', [UserController::class, 'edit'])->name('user.profile.edit');
    Route::put('/profile', [UserController::class, 'update'])->name('user.profile.update');

    // Produtos Auth
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');

    // Pedidos
    Route::get('/orders', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/orders/{id}', [UserController::class, 'showOrder'])->name('user.orders.show');
    Route::put('admin/orders/{order}', [OrderController::class, 'update'])->name('admin.orders.update');

    Route::get('/meus-preferidos', [UserPreferenceController::class, 'index'])->name('user.preferences');
    Route::post('/user/preferences/toggle', [UserPreferenceController::class, 'toggle'])->name('user.preferences.toggle');

    // --- Fluxo de Checkout Unificado ---
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success', [UserController::class, 'checkoutSuccess'])->name('checkout.success');
    Route::get('/checkout/error', fn() => view('checkout.error'))->name('checkout.error');
    Route::post('/cart/add-and-checkout', [CartController::class, 'addAndCheckout'])->name('cart.addAndCheckout');

    // PagoPar (Bancard / Pix / Outros)
    Route::prefix('pagopar')->group(function () {
        Route::get('/finish', [PagoParController::class, 'finish'])->name('pagopar.finish');
        Route::post('/callback', [PagoParController::class, 'callback'])->name('pagopar.callback');
    });

    // Métodos Offline
    Route::get('/checkout/deposito/{order}', [CheckoutController::class, 'deposito'])->name('checkout.deposito');
    Route::post('/checkout/deposito/{order}', [CheckoutController::class, 'submitDeposito'])->name('checkout.deposito.submit');
    Route::get('/checkout/whatsapp', [CheckoutController::class, 'whatsapp'])->name('checkout.whatsapp');
    Route::post('/orders/{order}/deposit', [OrderController::class, 'depositSubmit'])->name('orders.deposit.submit');

    // Cupons
    Route::get('cupons', [CuponUserController::class, 'index'])->name('user.cupons');
    Route::post('cupons/remove', [CuponUserController::class, 'remove'])->name('user.cupons.remove');
    Route::post('/user/cupon/apply', [CuponUserController::class, 'applyCupon'])->name('user.applyCupon');
    Route::post('/user/cupons/apply', [CuponUserController::class, 'apply'])->name('user.cupons.apply');

    Route::delete('/user/delete', [UserController::class, 'destroy'])->name('user.destroy');

    // // Verification
    // Route::get('/email/verify', fn() => view('auth.verify-email'))->name('verification.notice');
    // Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    //     $request->fulfill();
    //     return redirect('/home');
    // })->middleware(['signed'])->name('verification.verify');
    // Route::post('/email/verification-notification', function (Request $request) {
    //     $request->user()->sendEmailVerificationNotification();
    //     return back()->with('message', 'Link de verificação reenviado!');
    // })->middleware(['throttle:6,1'])->name('verification.send');
});

// --- Admin Routes ---
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [ImageUploadController::class, 'index'])->name('index');
    Route::get('dashboard', [UserController::class, 'dashboard'])->name('dashboard');

    Route::resource('palace', PalaceAdminController::class);
    Route::post('activate/update-all', [ActivateBrandsAndCategoriesController::class, 'updateAll'])->name('activate.updateAll');
    Route::resource('bridal', BridalAdminController::class);

    Route::post('products/{product}/toggle-status', [ProductControllerAdmin::class, 'toggleStatus'])->name('products.toggleStatus');
    Route::get('currencies', [CurrencyControllerAdmin::class, 'index'])->name('currencies.index');

    Route::get('activate-control', [ActivateBrandsAndCategoriesController::class, 'index'])->name('activate.index');
    Route::post('activate-toggle/{type}/{id}', [ActivateBrandsAndCategoriesController::class, 'toggleStatus'])->name('activate.toggle');

    Route::get('products/search', [ProductControllerAdmin::class, 'search'])->name('products.search');
    Route::get('sections-home', [AdminHighlightController::class, 'index'])->name('sections_home.index');
    Route::patch('sections-home/update', [AdminHighlightController::class, 'update'])->name('sections_home.update');

    Route::post('currencies', [CurrencyControllerAdmin::class, 'store'])->name('currencies.store');
    Route::resource('cupons', CuponController::class);
    Route::get('/produto/{product}', [ProductController::class, 'show'])->name('produto.show');
    Route::get('products/review', [ProductControllerAdmin::class, 'review'])->name('products.review');
    Route::put('currencies/{id}', [CurrencyControllerAdmin::class, 'update'])->name('currencies.update');
    Route::get('currencies/{id}/default', [CurrencyControllerAdmin::class, 'setDefault'])->name('currencies.default');
    Route::patch('products/{product}/update-highlights', [ProductControllerAdmin::class, 'updateHighlights'])->name('products.updateHighlights');

    Route::get('maintenance', [SystemController::class, 'maintenanceIndex'])->name('maintenance.index');
    Route::get('maintenance/toggle', [SystemController::class, 'toggleMaintenance'])->name('maintenance.toggle');

    // AJUSTE CATEGORIAS FILHAS ADMIN
    Route::get('products/subcategories/{category}', [ProductControllerAdmin::class, 'getSubcategories'])->name('products.getSubcategories');
    Route::get('products/categorias-filhas/{subcategory}', [ProductControllerAdmin::class, 'getCategoriasFilhas'])->name('products.getcategorias-filhas');

    Route::delete('categorias-filhas/{categorias_filha}/delete-photo', [CategoriasFilhasControllerAdmin::class, 'deletePhoto'])->name('categorias-filhas.deletePhoto');
    Route::delete('categorias-filhas/{categorias_filha}/delete-banner', [CategoriasFilhasControllerAdmin::class, 'deleteBanner'])->name('categorias-filhas.deleteBanner');

    // Rota para o "X" individual (Exclui uma por uma)
    Route::delete('products/{product}/gallery/{imageName}', [ProductControllerAdmin::class, 'deleteGalleryImage'])
        ->name('products.gallery.delete');

    // Rota para o Modal (Multi-delete)
    Route::delete('products/{product}/gallery-multi', [ProductControllerAdmin::class, 'multiDeleteGalleryImage'])
        ->name('products.gallery.multiDelete');

    // Resource unificado com parâmetro corrigido para o Controller
    Route::resource('categorias-filhas', CategoriasFilhasControllerAdmin::class)->parameters([
        'categorias-filhas' => 'categorias_filha'
    ]);

    Route::resource('subcategories', SubcategoryControllerAdmin::class);
    Route::delete('subcategories/{subcategory}/delete-photo', [SubcategoryControllerAdmin::class, 'deletePhoto'])->name('subcategories.deletePhoto');
    Route::delete('subcategories/{subcategory}/delete-banner', [SubcategoryControllerAdmin::class, 'deleteBanner'])->name('subcategories.deleteBanner');

    Route::resource('categories', CategoryControllerAdmin::class);
    Route::delete('categories/{category}/delete-photo', [CategoryControllerAdmin::class, 'deletePhoto'])->name('categories.deletePhoto');
    Route::delete('categories/{category}/delete-banner', [CategoryControllerAdmin::class, 'deleteBanner'])->name('categories.deleteBanner');
    Route::get('categories/convert-images', [CategoryControllerAdmin::class, 'convertCategoryImagesToWebp'])->name('categories.convertImages');

    Route::resource('orders', OrderController::class)->only(['index', 'show', 'destroy']);
    Route::resource('clients', ClientController::class)->only(['index', 'show']);
    Route::resource('payments', PaymentMethodController::class);
    Route::post('payments/{id}/toggle-active', [PaymentMethodController::class, 'toggleActive'])->name('payments.toggleActive');

    Route::resource('blogs', BlogControllerAdmin::class);
    Route::post('blogs/upload-image', [BlogControllerAdmin::class, 'uploadImage'])->name('blogs.upload-image');

    Route::resource('blog-categories', BlogCategoryController::class);
    Route::get('admin/blog-categories/{category}', [BlogCategoryController::class, 'show'])->name('admin.blog-categories.show');

    // Users
    Route::get('users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::post('users/{id}/update-type', [\App\Http\Controllers\Admin\UserController::class, 'updateType'])->name('users.updateType');
    Route::delete('users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');

    // Banners & Images
    Route::post('header/upload', [ImageUploadController::class, 'uploadHeader'])->name('header.upload');
    Route::delete('header/delete', [ImageUploadController::class, 'deleteHeader'])->name('header.delete');
    Route::post('noimage/upload', [ImageUploadController::class, 'uploadNoimage'])->name('noimage.upload');
    Route::delete('noimage/delete', [ImageUploadController::class, 'deleteNoimage'])->name('noimage.delete');

    // Banners 1 a 10
    foreach (range(1, 10) as $i) {
        Route::post("banner{$i}/upload", [ImageUploadController::class, "uploadBanner{$i}"])->name("banner{$i}.upload");
        Route::delete("banner{$i}/delete", [ImageUploadController::class, "deleteBanner{$i}"])->name("banner{$i}.delete");
    }

    // Novos Ícones de Atributos (Sistema)
    $iconTypes = ['icon_info', 'icon_cabide', 'icon_help'];

    foreach ($iconTypes as $icon) {
        Route::post("{$icon}/upload", [ImageUploadController::class, "upload" . Str::studly($icon)])->name("{$icon}.upload");
        Route::delete("{$icon}/delete", [ImageUploadController::class, "delete" . Str::studly($icon)])->name("{$icon}.delete");
    }

    Route::post('/logopalace/upload', [ImageUploadController::class, 'uploadLogoPalace'])->name('logopalace.upload');
    Route::delete('logopalace/delete', [ImageUploadController::class, 'deleteLogoPalace'])->name('logopalace.delete');

    Route::post('/logobridal/upload', [ImageUploadController::class, 'uploadLogoBridal'])->name('logobridal.upload');
    Route::delete('logobridal/delete', [ImageUploadController::class, 'deleteLogoBridal'])->name('logobridal.delete');

    Route::post('/bannerhorizontal/upload', [ImageUploadController::class, 'uploadBannerHorizontal'])->name('bannerhorizontal.upload');
    Route::delete('/bannerhorizontal/delete', [ImageUploadController::class, 'deleteBannerHorizontal'])->name('bannerhorizontal.delete');

    Route::resource('products', ProductControllerAdmin::class);
    Route::delete('products/{id}/photo', [ProductControllerAdmin::class, 'deletePhoto'])->name('products.deletePhoto');
    Route::delete('products/{id}/gallery/{image}', [ProductControllerAdmin::class, 'deleteGalleryImage'])->name('products.deleteGalleryImage');

    Route::resource('brands', BrandControllerAdmin::class);
    Route::delete('brands/{brand}/delete-logo', [BrandControllerAdmin::class, 'deleteLogo'])->name('brands.deleteLogo');
    Route::delete('brands/{brand}/delete-banner', [BrandControllerAdmin::class, 'deleteBanner'])->name('brands.deleteBanner');
    Route::delete('admin/brands/{brand}/delete-internal-banner', [BrandControllerAdmin::class, 'deleteInternalBanner'])->name('admin.brands.deleteInternalBanner');

    Route::resource('contatos', ContactControllerAdmin::class)->only(['index', 'destroy']);
    Route::get('contatos/export', [ContactControllerAdmin::class, 'export'])->name('contacts.export');

    // Verifique se está dentro de um grupo de prefixo 'admin'
    Route::post('clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
    Route::post('image-upload', [ImageUploadController::class, 'upload'])->name('image.upload');
    Route::delete('image-upload', [ImageUploadController::class, 'delete'])->name('image.delete');
    Route::get('image-upload', [ImageUploadController::class, 'form'])->name('image.form');
});

require __DIR__ . '/auth.php';
