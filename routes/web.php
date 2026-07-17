<?php

use App\Http\Controllers\Admin\ActivateBrandsAndCategoriesController;
use App\Http\Controllers\Admin\AdminHighlightController;
use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogControllerAdmin;
use App\Http\Controllers\Admin\BrandControllerAdmin;
use App\Http\Controllers\Admin\BridalAdminController;
use App\Http\Controllers\Admin\CafeBistroAdminController;
use App\Http\Controllers\Admin\CategoriasFilhasControllerAdmin;
use App\Http\Controllers\Admin\CategoryControllerAdmin;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ContactControllerAdmin;
use App\Http\Controllers\Admin\CuponController;
use App\Http\Controllers\Admin\CurrencyControllerAdmin;
use App\Http\Controllers\Admin\InstitucionalAdminController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PalaceAdminController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ProductControllerAdmin;
use App\Http\Controllers\Admin\SubcategoryControllerAdmin;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\AllCategoriesController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\UserPreferenceController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\BridalController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CafeBistroController;
use App\Http\Controllers\CategoriasFilhasController as PublicCategoriasFilhasController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CuponUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\InstitucionalController;
use App\Http\Controllers\PalaceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SubcategoryController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::get('/testar-email', function () {
    try {
        Mail::raw('Opa! Se você recebeu isso, o SMTP da Umbler está funcionando no Laravel.', function ($message) {
            $message->to('eduustcc@gmail.com')
                ->subject('Teste de SMTP - Sax Department');
        });

        return 'Sucesso! O e-mail foi enviado.';
    } catch (\Exception $e) {
        return 'Erro ao enviar: ' . $e->getMessage();
    }
});

Route::fallback(function () {
    $url = request()->path();
    $jsonPath = public_path('data/routes.json');

    if (file_exists($jsonPath)) {
        $map = json_decode(file_get_contents($jsonPath), true);

        foreach ($map as $key => $route) {
            if (str_contains($url, $key)) {
                return redirect()->route($route);
            }
        }
    }

    return response()->view('error.404', [], 404);
});

Route::get('/mail-preview/order-paid', function () {
    $order = \App\Models\Order::latest()->first();

    return new \App\Mail\OrderPaidMail($order);
});

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/ajax', [SearchController::class, 'ajaxSearch'])->name('search.ajax');
Route::get('/search/autocomplete', [SearchController::class, 'autocomplete'])->name('search.autocomplete');
Route::get('/institucional', [InstitucionalController::class, 'index'])->name('institucional.index');
Route::get('/manutencao', fn () => view('manutencao.index'))->name('maintenance.page');
Route::get('/palace', [PalaceController::class, 'index'])->name('palace.index');
Route::get('/bridal', [BridalController::class, 'index'])->name('bridal.index');
Route::get('/bistro', [CafeBistroController::class, 'index'])->name('cafe_bistro.index');
Route::post('/newsletter', [HomeController::class, 'storeNewsletter'])->name('newsletter.store');
Route::get('/categorias-gerais', [AllCategoriesController::class, 'index'])->name('all-categories.index');
Route::redirect('/produtos', '/search')->name('produtos.index');
Route::get('/produto/{id_or_slug}', [ProductController::class, 'show'])->name('produto.show');
Route::get('/categorias/{category}/produtos', [ProductController::class, 'byCategory'])->name('products.byCategory');
Route::get('/subcategorias/{subcategory}/produtos', [ProductController::class, 'bySubcategory'])->name('products.bySubcategory');
Route::get('/categorias-filhas/{categoriasfilhas}/produtos', [ProductController::class, 'byCategoriasFilhas'])->name('products.byCategoriasFilhas');
Route::get('/categorias', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categorias/{slug}', [CategoryController::class, 'show'])->name('categories.show');
Route::get('/subcategorias', [SubcategoryController::class, 'index'])->name('subcategories.index');
Route::get('/subcategorias/{slug}', [SubcategoryController::class, 'show'])->name('subcategories.show');
Route::get('/categorias-filhas', [PublicCategoriasFilhasController::class, 'index'])->name('categorias-filhas.index');
Route::get('/categorias-filhas/{slug}', [PublicCategoriasFilhasController::class, 'show'])->name('categorias-filhas.show');
Route::get('/marcas', [BrandController::class, 'publicIndex'])->name('brands.index');
Route::get('/marcas/{slug}', [BrandController::class, 'publicShow'])->name('brands.show');
Route::get('/cart', [CartController::class, 'view'])->name('cart.view');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::match(['post', 'put'], '/cart/update/{productId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove/{productId}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/ajax-search', [BlogController::class, 'ajaxSearch'])->name('blogs.ajax-search');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');
Route::get('/contato', [ContactController::class, 'showForm'])->name('contact.form');
Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');
Route::get('/politicas', [PolicyController::class, 'index'])->name('policies.index');
Route::post('/currency/change', [CurrencyController::class, 'change'])->name('currency.change');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/profile', [UserController::class, 'edit'])->name('user.profile.edit');
    Route::put('/profile', [UserController::class, 'update'])->name('user.profile.update');
    Route::post('/checkout/calcular-frete', [CheckoutController::class, 'ajaxCalcularFrete'])->name('checkout.calcular-frete');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/orders', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/orders/{id}', [UserController::class, 'showOrder'])->name('user.orders.show');
    Route::get('/receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
    Route::get('/receipts/{receipt}/download', [ReceiptController::class, 'download'])->name('receipts.download');
    Route::get('/meus-preferidos', [UserPreferenceController::class, 'index'])->name('user.preferences');
    Route::post('/user/preferences/toggle', [UserPreferenceController::class, 'toggle'])->name('user.preferences.toggle');
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/checkout/success', [UserController::class, 'checkoutSuccess'])->name('checkout.success');
    Route::get('/checkout/error', fn () => view('checkout.error'))->name('checkout.error');
    Route::post('/cart/add-and-checkout', [CartController::class, 'addAndCheckout'])->name('cart.addAndCheckout');
    Route::delete('/cart/abandon', [CartController::class, 'abandon'])->name('cart.abandon');
    Route::get('/carrinhos-abandonados', [\App\Http\Controllers\AbandonedCartController::class, 'index'])->name('user.abandoned-carts.index');
    Route::get('/carrinhos-abandonados/{abandonedCart}', [\App\Http\Controllers\AbandonedCartController::class, 'show'])->name('user.abandoned-carts.show');
    Route::post('/carrinhos-abandonados/{abandonedCart}/restaurar', [\App\Http\Controllers\AbandonedCartController::class, 'restore'])->name('user.abandoned-carts.restore');

    Route::get('/checkout/bancard-v2/{order}', [\App\Http\Controllers\BancardV2Controller::class, 'checkoutPage'])
        ->whereNumber('order')
        ->name('checkout.bancard.v2');
    Route::get('/checkout/bancard-v2/{order}/cancel', [\App\Http\Controllers\BancardV2Controller::class, 'cancelCheckout'])
        ->whereNumber('order')
        ->name('checkout.bancard.v2.cancel');

    Route::get('/checkout/deposito/{order}', [CheckoutController::class, 'deposito'])->name('checkout.deposito');
    Route::post('/checkout/deposito/{order}', [CheckoutController::class, 'submitDeposito'])->name('checkout.deposito.submit');
    Route::get('/checkout/whatsapp', [CheckoutController::class, 'whatsapp'])->name('checkout.whatsapp');
    Route::post('/orders/{order}/deposit', [OrderController::class, 'depositSubmit'])->name('orders.deposit.submit');
    Route::get('cupons', [CuponUserController::class, 'index'])->name('user.cupons');
    Route::post('cupons/remove', [CuponUserController::class, 'remove'])->name('user.cupons.remove');
    Route::post('/user/cupon/apply', [CuponUserController::class, 'applyCupon'])->name('user.applyCupon');
    Route::post('/user/cupons/apply', [CuponUserController::class, 'apply'])->name('user.cupons.apply');
    Route::delete('/user/delete', [UserController::class, 'destroy'])->name('user.destroy');
});

Route::post('/checkout/bancard-v2/callback', [\App\Http\Controllers\BancardV2Controller::class, 'callback'])->name('bancard.v2.callback');
Route::get('/checkout/bancard-v2/finish', [\App\Http\Controllers\BancardV2Controller::class, 'returnPage'])->name('bancard.v2.return');
Route::get('/checkout/bancard-v2/success', [\App\Http\Controllers\BancardV2Controller::class, 'successPage'])->name('bancard.v2.success');
Route::get('/checkout/bancard-v2/error', [\App\Http\Controllers\BancardV2Controller::class, 'errorPage'])->name('bancard.v2.error');

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [ImageUploadController::class, 'index'])->name('index');
    Route::get('dashboard', [UserController::class, 'dashboard'])->name('dashboard');
    Route::resource('languages', \App\Http\Controllers\Admin\LanguageControllerAdmin::class);
    Route::put('attributes/text-topo', [ImageUploadController::class, 'updateTextTopo'])->name('attributes.update_text');
    Route::put('attributes/banner-links', [ImageUploadController::class, 'updateBannerLinks'])->name('attributes.update_banner_links');
    Route::resource('palace', PalaceAdminController::class);
    // O salvar-tudo em lote saiu: cada item agora é alternado por AJAX (activate.toggle).
    Route::resource('bridal', BridalAdminController::class);
    Route::resource('cafe_bistro', CafeBistroAdminController::class);
    Route::resource('institucional', InstitucionalAdminController::class);
    Route::post('products/{product}/toggle-status', [ProductControllerAdmin::class, 'toggleStatus'])->name('products.toggleStatus');
    Route::post('products/revalidate-status', [ProductControllerAdmin::class, 'revalidateStatus'])->name('products.revalidateStatus');
    Route::get('currencies', [CurrencyControllerAdmin::class, 'index'])->name('currencies.index');
    Route::get('activate-control', [ActivateBrandsAndCategoriesController::class, 'index'])->name('activate.index');
    Route::post('activate-toggle/{type}/{id}', [ActivateBrandsAndCategoriesController::class, 'toggleStatus'])->name('activate.toggle');
    Route::get('products/search', [ProductControllerAdmin::class, 'search'])->name('products.search');
    Route::get('sections-home', [AdminHighlightController::class, 'index'])->name('sections_home.index');
    Route::patch('sections-home/update', [AdminHighlightController::class, 'update'])->name('sections_home.update');
    Route::post('currencies', [CurrencyControllerAdmin::class, 'store'])->name('currencies.store');
    // Antes do resource: 'cupons/produtos' não pode cair na rota 'cupons/{cupon}'.
    Route::get('cupons/produtos', [CuponController::class, 'buscarProdutos'])->name('cupons.produtos');
    Route::resource('cupons', CuponController::class);
    Route::patch('cupons/{cupon}/toggle', [CuponController::class, 'toggle'])->name('cupons.toggle');
    Route::get('/produto/{product}', [ProductController::class, 'show'])->name('produto.show');
    Route::get('products/review', [ProductControllerAdmin::class, 'review'])->name('products.review');
    Route::put('currencies/{id}', [CurrencyControllerAdmin::class, 'update'])->name('currencies.update');
    Route::get('currencies/{id}/default', [CurrencyControllerAdmin::class, 'setDefault'])->name('currencies.default');
    Route::patch('products/{product}/update-highlights', [ProductControllerAdmin::class, 'updateHighlights'])->name('products.updateHighlights');
    Route::get('maintenance', [SystemController::class, 'maintenanceIndex'])->name('maintenance.index');
    Route::get('maintenance/toggle', [SystemController::class, 'toggleMaintenance'])->name('maintenance.toggle');
    Route::get('products/subcategories/{category}', [ProductControllerAdmin::class, 'getSubcategories'])->name('products.getSubcategories');
    Route::get('products/categorias-filhas/{subcategory}', [ProductControllerAdmin::class, 'getChildcategories'])->name('products.getcategorias-filhas');
    Route::delete('categorias-filhas/{categorias_filha}/delete-photo', [CategoriasFilhasControllerAdmin::class, 'deletePhoto'])->name('categorias-filhas.deletePhoto');
    Route::delete('categorias-filhas/{categorias_filha}/delete-banner', [CategoriasFilhasControllerAdmin::class, 'deleteBanner'])->name('categorias-filhas.deleteBanner');
    Route::post('categorias-filhas/{categorias_filha}/upload-photo', [CategoriasFilhasControllerAdmin::class, 'uploadPhoto'])->name('categorias-filhas.uploadPhoto');
    Route::post('categorias-filhas/{categorias_filha}/upload-banner', [CategoriasFilhasControllerAdmin::class, 'uploadBanner'])->name('categorias-filhas.uploadBanner');
    Route::delete('products/{product}/gallery/{imageName}', [ProductControllerAdmin::class, 'deleteGalleryImage'])
        ->name('products.gallery.delete');
    Route::delete('products/{product}/gallery-multi', [ProductControllerAdmin::class, 'multiDeleteGalleryImage'])
        ->name('products.gallery.multiDelete');
    Route::resource('categorias-filhas', CategoriasFilhasControllerAdmin::class)->parameters([
        'categorias-filhas' => 'categorias_filha',
    ]);
    Route::resource('subcategories', SubcategoryControllerAdmin::class);
    Route::delete('subcategories/{subcategory}/delete-photo', [SubcategoryControllerAdmin::class, 'deletePhoto'])->name('subcategories.deletePhoto');
    Route::delete('subcategories/{subcategory}/delete-banner', [SubcategoryControllerAdmin::class, 'deleteBanner'])->name('subcategories.deleteBanner');
    Route::post('subcategories/{subcategory}/upload-photo', [SubcategoryControllerAdmin::class, 'uploadPhoto'])->name('subcategories.uploadPhoto');
    Route::post('subcategories/{subcategory}/upload-banner', [SubcategoryControllerAdmin::class, 'uploadBanner'])->name('subcategories.uploadBanner');
    Route::resource('categories', CategoryControllerAdmin::class);
    Route::delete('categories/{category}/delete-photo', [CategoryControllerAdmin::class, 'deletePhoto'])->name('categories.deletePhoto');
    Route::delete('categories/{category}/delete-banner', [CategoryControllerAdmin::class, 'deleteBanner'])->name('categories.deleteBanner');
    Route::post('categories/{id}/upload-photo', [CategoryControllerAdmin::class, 'uploadPhoto'])->name('categories.uploadPhoto');
    Route::post('categories/{id}/upload-banner', [CategoryControllerAdmin::class, 'uploadBanner'])->name('categories.uploadBanner');
    Route::get('categories/convert-images', [CategoryControllerAdmin::class, 'convertCategoryImagesToWebp'])->name('categories.convertImages');
    Route::resource('orders', OrderController::class)->only(['index', 'show', 'destroy']);
    Route::resource('clients', ClientController::class)->only(['index', 'show']);
    Route::resource('abandoned-carts', \App\Http\Controllers\Admin\AbandonedCartControllerAdmin::class)
        ->parameters(['abandoned-carts' => 'abandonedCart'])
        ->only(['index', 'show']);
    Route::resource('payments', PaymentMethodController::class);
    Route::post('payments/{id}/toggle-active', [PaymentMethodController::class, 'toggleActive'])->name('payments.toggleActive');
    Route::put('orders/{order}', [OrderController::class, 'update'])->name('orders.update');
    Route::resource('blogs', BlogControllerAdmin::class);
    Route::resource('policies', \App\Http\Controllers\Admin\PolicyControllerAdmin::class)->only(['index', 'edit', 'update']);
    Route::post('blogs/upload-image', [BlogControllerAdmin::class, 'uploadImage'])->name('blogs.upload-image');
    Route::resource('blog-categories', BlogCategoryController::class)->parameter('blog-categories', 'category');
    Route::get('users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('users.create');
    Route::post('users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('users.store');
    Route::post('users/{id}/update-type', [\App\Http\Controllers\Admin\UserController::class, 'updateType'])->name('users.updateType');
    Route::delete('users/{id}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('users.destroy');
    Route::post('header/upload', [ImageUploadController::class, 'uploadHeader'])->name('header.upload');
    Route::delete('header/delete', [ImageUploadController::class, 'deleteHeader'])->name('header.delete');
    Route::post('noimage/upload', [ImageUploadController::class, 'uploadNoimage'])->name('noimage.upload');
    Route::delete('noimage/delete', [ImageUploadController::class, 'deleteNoimage'])->name('noimage.delete');
    Route::post('whatsapp_banner/upload', [ImageUploadController::class, 'uploadWhatsappBanner'])->name('whatsapp_banner.upload');
    Route::delete('whatsapp_banner/delete', [ImageUploadController::class, 'deleteWhatsappBanner'])->name('whatsapp_banner.delete');

    foreach (range(1, 10) as $i) {
        Route::post("banner{$i}/upload", [ImageUploadController::class, "uploadBanner{$i}"])->name("banner{$i}.upload");
        Route::delete("banner{$i}/delete", [ImageUploadController::class, "deleteBanner{$i}"])->name("banner{$i}.delete");
    }

    $iconTypes = ['icon_info', 'icon_cabide', 'icon_help'];

    foreach ($iconTypes as $icon) {
        Route::post("{$icon}/upload", [ImageUploadController::class, 'upload' . Str::studly($icon)])->name("{$icon}.upload");
        Route::delete("{$icon}/delete", [ImageUploadController::class, 'delete' . Str::studly($icon)])->name("{$icon}.delete");
    }

    Route::post('/logopalace/upload', [ImageUploadController::class, 'uploadLogoPalace'])->name('logopalace.upload');
    Route::delete('logopalace/delete', [ImageUploadController::class, 'deleteLogoPalace'])->name('logopalace.delete');
    Route::post('/logobridal/upload', [ImageUploadController::class, 'uploadLogoBridal'])->name('logobridal.upload');
    Route::delete('logobridal/delete', [ImageUploadController::class, 'deleteLogoBridal'])->name('logobridal.delete');
    Route::post('/logocafebistro/upload', [ImageUploadController::class, 'uploadLogoCafeBistro'])->name('logocafebistro.upload');
    Route::delete('/logocafebistro/delete', [ImageUploadController::class, 'deleteLogoCafeBistro'])->name('logocafebistro.delete');
    Route::post('/bannerhorizontal/upload', [ImageUploadController::class, 'uploadBannerHorizontal'])->name('bannerhorizontal.upload');
    Route::delete('/bannerhorizontal/delete', [ImageUploadController::class, 'deleteBannerHorizontal'])->name('bannerhorizontal.delete');
    Route::resource('products', ProductControllerAdmin::class);
    Route::delete('products/{id}/photo', [ProductControllerAdmin::class, 'deletePhoto'])->name('products.deletePhoto');
    Route::delete('products/{id}/gallery/{image}', [ProductControllerAdmin::class, 'deleteGalleryImage'])->name('products.deleteGalleryImage');
    Route::resource('brands', BrandControllerAdmin::class);
    Route::delete('brands/{brand}/delete-logo', [BrandControllerAdmin::class, 'deleteLogo'])->name('brands.deleteLogo');
    Route::delete('brands/{brand}/delete-banner', [BrandControllerAdmin::class, 'deleteBanner'])->name('brands.deleteBanner');
    Route::delete('brands/{brand}/delete-internal-banner', [BrandControllerAdmin::class, 'deleteInternalBanner'])->name('brands.deleteInternalBanner');
    Route::post('brands/{brand}/upload-logo', [BrandControllerAdmin::class, 'uploadLogo'])->name('brands.uploadLogo');
    Route::post('brands/{brand}/upload-banner', [BrandControllerAdmin::class, 'uploadBanner'])->name('brands.uploadBanner');
    Route::post('brands/{brand}/upload-internal-banner', [BrandControllerAdmin::class, 'uploadInternalBanner'])->name('brands.uploadInternalBanner');
    Route::resource('contatos', ContactControllerAdmin::class)->only(['index', 'destroy']);
    Route::get('contatos/export', [ContactControllerAdmin::class, 'export'])->name('contacts.export');
    Route::post('clear-cache', [SystemController::class, 'clearCache'])->name('clear-cache');
    Route::post('image-upload', [ImageUploadController::class, 'upload'])->name('image.upload');
    Route::delete('image-upload', [ImageUploadController::class, 'delete'])->name('image.delete');
    Route::get('image-upload', [ImageUploadController::class, 'form'])->name('image.form');
});

// Troca apenas o idioma. A cotação é independente (currency.change).
Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, \App\Http\Middleware\SetLocale::LOCALES, true)) {
        session()->put('locale', $locale);
        session()->save();
    }

    return redirect()->back();
})->name('lang.switch');

require __DIR__ . '/auth.php';
