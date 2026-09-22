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
use App\Http\Controllers\Admin\EmailMarketingController;
use App\Http\Controllers\Admin\ContactGuideController as AdminContactGuideController;
use App\Http\Controllers\Admin\CuponController;
use App\Http\Controllers\Admin\CurrencyControllerAdmin;
use App\Http\Controllers\Admin\InstitucionalAdminController;
use App\Http\Controllers\Admin\JobFlyerControllerAdmin;
use App\Http\Controllers\Admin\MarketingSettingController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PalaceAdminController;
use App\Http\Controllers\Admin\PaymentMethodController;
use App\Http\Controllers\Admin\ProductControllerAdmin;
use App\Http\Controllers\Admin\ProductFeedController as AdminProductFeedController;
use App\Http\Controllers\Admin\ProductAiBatchController;
use App\Http\Controllers\Admin\SubcategoryControllerAdmin;
use App\Http\Controllers\Admin\SystemController;
use App\Http\Controllers\Admin\StoreControlController;
use App\Http\Controllers\Admin\ThemeSettingController;
use App\Http\Controllers\Admin\WhatsappWidgetController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DhlSettingController;
use App\Http\Controllers\Admin\DhlMeasurementRuleController;
use App\Http\Controllers\Admin\HomeBannerController;
use App\Http\Controllers\AllCategoriesController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\UserAddressController;
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
use App\Http\Controllers\ContactGuideController;
use App\Http\Controllers\EmailUnsubscribeController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\CuponUserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ImageUploadController;
use App\Http\Controllers\InstitucionalController;
use App\Http\Controllers\PalaceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductFeedController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SiteAnalyticsController;
use App\Http\Controllers\SubcategoryController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

Route::post('/analytics/event', [SiteAnalyticsController::class, 'store'])
    ->middleware('throttle:120,1')
    ->name('analytics.store');

Route::get('/email/preferencias/{token}', EmailUnsubscribeController::class)
    ->where('token', '[A-Za-z0-9]{64}')
    ->middleware('throttle:30,1')
    ->name('email.unsubscribe');

Route::get('/feeds/products.xml', [ProductFeedController::class, 'show'])
    ->name('product-feed.show');

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
Route::get('/colecoes/{collection}', [SearchController::class, 'collection'])
    ->whereIn('collection', ['new-arrivals', 'trending'])
    ->name('collections.show');
Route::get('/institucional', [InstitucionalController::class, 'index'])->name('institucional.index');
Route::get('/manutencao', fn () => view('manutencao.index'))->name('maintenance.page');
Route::get('/palace', [PalaceController::class, 'index'])->name('palace.index');
Route::get('/bridal', [BridalController::class, 'index'])->name('bridal.index');
Route::get('/bistro', [CafeBistroController::class, 'index'])->name('cafe_bistro.index');
Route::get('/bistro/{location}', [CafeBistroController::class, 'show'])
    ->whereIn('location', ['pedro-juan-caballero', 'asuncion'])
    ->name('cafe_bistro.show');
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
Route::get('/cart', [CartController::class, 'view'])->middleware(['auth', 'store.feature:cart'])->name('cart.view');
Route::post('/cart/add', [CartController::class, 'add'])->middleware(['store.feature:add_to_cart'])->name('cart.add');
Route::match(['post', 'put'], '/cart/update/{productId}', [CartController::class, 'update'])->middleware('store.feature:cart')->name('cart.update');
Route::delete('/cart/remove/{productId}', [CartController::class, 'remove'])->middleware('store.feature:cart')->name('cart.remove');
Route::get('/blogs', [BlogController::class, 'index'])->name('blogs.index');
Route::get('/blogs/ajax-search', [BlogController::class, 'ajaxSearch'])->name('blogs.ajax-search');
Route::get('/blogs/{slug}', [BlogController::class, 'show'])->name('blogs.show');
Route::get('/contato', [ContactController::class, 'showForm'])->name('contact.form');
Route::post('/contato', [ContactController::class, 'store'])->name('contact.store');
Route::get('/guia-de-atendimento', [ContactGuideController::class, 'show'])->name('contact.guide');
Route::get('/guia-de-atendimento-2', [ContactGuideController::class, 'alternative'])->name('contact.guide.alternative');
Route::get('/politicas', [PolicyController::class, 'index'])->name('policies.index');
Route::post('/currency/change', [CurrencyController::class, 'change'])->name('currency.change');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [UserController::class, 'dashboard'])->name('user.dashboard');
    Route::get('/profile', [UserController::class, 'edit'])->name('user.profile.edit');
    Route::put('/profile', [UserController::class, 'update'])->name('user.profile.update');
    Route::get('/meus-enderecos', [UserAddressController::class, 'index'])->name('user.addresses.index');
    Route::post('/meus-enderecos', [UserAddressController::class, 'store'])->name('user.addresses.store');
    Route::patch('/meus-enderecos/{address}', [UserAddressController::class, 'update'])->name('user.addresses.update');
    Route::patch('/meus-enderecos/{address}/padrao', [UserAddressController::class, 'makeDefault'])->name('user.addresses.default');
    Route::delete('/meus-enderecos/{address}', [UserAddressController::class, 'destroy'])->name('user.addresses.destroy');
    Route::get('/seguranca/senha', [UserController::class, 'editPassword'])->name('user.password.edit');
    Route::put('/seguranca/senha', [UserController::class, 'updatePassword'])->name('user.password.update');
    Route::post('/checkout/calcular-frete', [CheckoutController::class, 'ajaxCalcularFrete'])->middleware('throttle:30,1')->name('checkout.calcular-frete');
    Route::get('/products/{id}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/orders', [UserController::class, 'orders'])->name('user.orders');
    Route::get('/orders/{id}', [UserController::class, 'showOrder'])->name('user.orders.show');
    Route::post('/orders/{order}/rendix-refund-request', [\App\Http\Controllers\RendixRefundRequestController::class, 'store'])
        ->whereNumber('order')
        ->name('user.orders.rendix-refund-request');
    Route::get('/receipts/{receipt}', [ReceiptController::class, 'show'])->name('receipts.show');
    Route::get('/receipts/{receipt}/download', [ReceiptController::class, 'download'])->name('receipts.download');
    Route::get('/meus-preferidos', [UserPreferenceController::class, 'index'])->name('user.preferences');
    Route::post('/user/preferences/toggle', [UserPreferenceController::class, 'toggle'])->name('user.preferences.toggle');
    Route::get('/checkout', [CheckoutController::class, 'index'])->middleware(['store.feature:checkout', 'catalog.healthy'])->name('checkout.index');
    Route::post('/checkout/store', [CheckoutController::class, 'store'])->middleware(['store.feature:checkout', 'catalog.healthy'])->name('checkout.store');
    Route::get('/checkout/success', [UserController::class, 'checkoutSuccess'])->name('checkout.success');
    Route::get('/checkout/error', fn () => view('checkout.error'))->name('checkout.error');
    Route::post('/cart/add-and-checkout', [CartController::class, 'addAndCheckout'])->middleware(['store.feature:add_to_cart', 'store.feature:checkout', 'catalog.healthy'])->name('cart.addAndCheckout');
    Route::delete('/cart/abandon', [CartController::class, 'abandon'])->name('cart.abandon');
    Route::get('/carrinhos-abandonados', [\App\Http\Controllers\AbandonedCartController::class, 'index'])->name('user.abandoned-carts.index');
    Route::get('/carrinhos-abandonados/{abandonedCart}', [\App\Http\Controllers\AbandonedCartController::class, 'show'])->name('user.abandoned-carts.show');
    Route::post('/carrinhos-abandonados/{abandonedCart}/restaurar', [\App\Http\Controllers\AbandonedCartController::class, 'restore'])->middleware(['store.feature:add_to_cart'])->name('user.abandoned-carts.restore');

    Route::get('/checkout/bancard-v2/{order}', [\App\Http\Controllers\BancardV2Controller::class, 'checkoutPage'])
        ->middleware(['store.feature:checkout', 'store.feature:bancard', 'catalog.healthy'])
        ->whereNumber('order')
        ->name('checkout.bancard.v2');
    Route::get('/checkout/bancard-v2/{order}/cancel', [\App\Http\Controllers\BancardV2Controller::class, 'cancelCheckout'])
        ->whereNumber('order')
        ->name('checkout.bancard.v2.cancel');

    Route::get('/checkout/pix/{order}', [\App\Http\Controllers\RendixPixController::class, 'checkoutPage'])
        ->middleware(['store.feature:checkout', 'store.feature:pix', 'catalog.healthy'])
        ->whereNumber('order')
        ->name('checkout.rendix.pix');
    Route::get('/checkout/pix/{order}/status', [\App\Http\Controllers\RendixPixController::class, 'status'])
        ->middleware(['store.feature:pix', 'catalog.healthy', 'throttle:20,1'])
        ->whereNumber('order')
        ->name('checkout.rendix.pix.status');
    Route::post('/checkout/pix/{order}/renovar', [\App\Http\Controllers\RendixPixController::class, 'renew'])
        ->middleware(['store.feature:checkout', 'store.feature:pix', 'catalog.healthy', 'throttle:5,1'])
        ->whereNumber('order')
        ->name('checkout.rendix.pix.renew');
    Route::get('/checkout/pix/termos/rendix', [\App\Http\Controllers\RendixPixController::class, 'terms'])
        ->middleware('throttle:10,1')
        ->name('checkout.rendix.pix.terms');

    Route::get('/checkout/deposito/{order}', [CheckoutController::class, 'deposito'])->middleware(['store.feature:checkout', 'store.feature:deposit', 'catalog.healthy'])->name('checkout.deposito');
    Route::post('/checkout/deposito/{order}', [CheckoutController::class, 'submitDeposito'])->middleware(['store.feature:checkout', 'store.feature:deposit', 'catalog.healthy'])->name('checkout.deposito.submit');
    Route::get('/cart/whatsapp', [CartController::class, 'whatsapp'])->middleware('store.feature:whatsapp')->name('cart.whatsapp');
    Route::get('/checkout/whatsapp', [CheckoutController::class, 'whatsapp'])->middleware(['store.feature:checkout', 'catalog.healthy'])->name('checkout.whatsapp');
    Route::post('/orders/{order}/deposit', [OrderController::class, 'depositSubmit'])->middleware(['store.feature:checkout', 'store.feature:deposit', 'catalog.healthy'])->name('orders.deposit.submit');
    Route::get('cupons', [CuponUserController::class, 'index'])->name('user.cupons');
    Route::post('notifications/read-all', [\App\Http\Controllers\Auth\UserNotificationController::class, 'markAllAsRead'])->name('user.notifications.read-all');
    Route::post('notifications/{notification}/read', [\App\Http\Controllers\Auth\UserNotificationController::class, 'markAsRead'])->whereNumber('notification')->name('user.notifications.read');
    Route::post('cupons/remove', [CuponUserController::class, 'remove'])->name('user.cupons.remove');
    Route::post('/user/cupon/apply', [CuponUserController::class, 'applyCupon'])->name('user.applyCupon');
    Route::post('/user/cupons/apply', [CuponUserController::class, 'apply'])->name('user.cupons.apply');
    Route::delete('/user/delete', [UserController::class, 'destroy'])->name('user.destroy');
});

Route::post('/checkout/bancard-v2/callback', [\App\Http\Controllers\BancardV2Controller::class, 'callback'])->name('bancard.v2.callback');
Route::post('/webhooks/rendix/pix', [\App\Http\Controllers\RendixPixController::class, 'webhook'])
    ->middleware('throttle:60,1')
    ->name('rendix.pix.webhook');
Route::get('/checkout/bancard-v2/finish', [\App\Http\Controllers\BancardV2Controller::class, 'returnPage'])->name('bancard.v2.return');
Route::get('/checkout/bancard-v2/success', [\App\Http\Controllers\BancardV2Controller::class, 'successPage'])->name('bancard.v2.success');
Route::get('/checkout/bancard-v2/error', [\App\Http\Controllers\BancardV2Controller::class, 'errorPage'])->name('bancard.v2.error');
Route::get('/ajuda-carrinho/{token}', [\App\Http\Controllers\AbandonedCartFeedbackController::class, 'show'])
    ->name('abandoned-cart.feedback');
Route::post('/ajuda-carrinho/{token}', [\App\Http\Controllers\AbandonedCartFeedbackController::class, 'store'])
    ->middleware('throttle:10,1')
    ->name('abandoned-cart.feedback.store');

Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('index');
    Route::get('/dashboard/insights', [DashboardController::class, 'insights'])->name('dashboard.insights');
    Route::get('/dashboard/countries', [DashboardController::class, 'countries'])->name('dashboard.countries');
    Route::get('/relatorios/{period?}', [DashboardController::class, 'report'])->whereIn('period', ['today', 'week', 'month'])->name('reports.download');
    Route::get('banners', [ImageUploadController::class, 'index'])->name('banners.index');
    Route::post('banners/home/{group}', [HomeBannerController::class, 'store'])
        ->whereIn('group', ['main', 'editorial'])->name('home-banners.store');
    Route::patch('banners/home/{homeBanner}', [HomeBannerController::class, 'update'])
        ->whereNumber('homeBanner')->name('home-banners.update');
    Route::post('banners/home/{homeBanner}/images', [HomeBannerController::class, 'updateImages'])
        ->whereNumber('homeBanner')->name('home-banners.images');
    Route::put('banners/home/{group}/ordem', [HomeBannerController::class, 'reorder'])
        ->whereIn('group', ['main', 'editorial'])->name('home-banners.reorder');
    Route::delete('banners/home/{homeBanner}', [HomeBannerController::class, 'destroy'])
        ->whereNumber('homeBanner')->name('home-banners.destroy');
    Route::get('marketing', [MarketingSettingController::class, 'edit'])->name('marketing.edit');
    Route::put('marketing', [MarketingSettingController::class, 'update'])->name('marketing.update');
    Route::get('identidade-visual', [ThemeSettingController::class, 'edit'])->name('theme-settings.edit');
    Route::post('identidade-visual/favicon/{layout}', [\App\Http\Controllers\Admin\StorefrontFaviconController::class, 'update'])
        ->whereIn('layout', array_keys(\App\Services\StorefrontLayoutService::LAYOUTS))
        ->name('theme-settings.favicon.update');
    Route::delete('identidade-visual/favicon/{layout}', [\App\Http\Controllers\Admin\StorefrontFaviconController::class, 'destroy'])
        ->whereIn('layout', array_keys(\App\Services\StorefrontLayoutService::LAYOUTS))
        ->name('theme-settings.favicon.destroy');
    Route::put('identidade-visual/{scope}', [ThemeSettingController::class, 'update'])
        ->whereIn('scope', array_keys(\App\Services\ThemeSettingsService::SCOPES))
        ->middleware('throttle:120,1')
        ->name('theme-settings.update');
    Route::delete('identidade-visual/{scope}', [ThemeSettingController::class, 'reset'])
        ->whereIn('scope', array_keys(\App\Services\ThemeSettingsService::SCOPES))
        ->name('theme-settings.reset');
    Route::get('controle-loja', [StoreControlController::class, 'edit'])->name('store-controls.edit');
    Route::get('configuracion-ia', [\App\Http\Controllers\Admin\ProductAiSettingsController::class, 'edit'])->name('ai-settings.edit');
    Route::put('configuracion-ia', [\App\Http\Controllers\Admin\ProductAiSettingsController::class, 'update'])->name('ai-settings.update');
    Route::put('controle-loja', [StoreControlController::class, 'update'])->name('store-controls.update');
    Route::get('whatsapp', [WhatsappWidgetController::class, 'edit'])->name('whatsapp.edit');
    Route::put('whatsapp/settings', [WhatsappWidgetController::class, 'updateSettings'])->name('whatsapp.settings.update');
    Route::post('whatsapp/contacts', [WhatsappWidgetController::class, 'storeContact'])->name('whatsapp.contacts.store');
    Route::put('whatsapp/contacts/{contact}', [WhatsappWidgetController::class, 'updateContact'])
        ->whereNumber('contact')->name('whatsapp.contacts.update');
    Route::delete('whatsapp/contacts/{contact}', [WhatsappWidgetController::class, 'destroyContact'])
        ->whereNumber('contact')->name('whatsapp.contacts.destroy');
    Route::get('guia-de-atendimento', [AdminContactGuideController::class, 'index'])->name('contact-guide.index');
    Route::post('guia-de-atendimento/unidades', [AdminContactGuideController::class, 'storeLocation'])->name('contact-guide.locations.store');
    Route::put('guia-de-atendimento/unidades/{location}', [AdminContactGuideController::class, 'updateLocation'])->whereNumber('location')->name('contact-guide.locations.update');
    Route::delete('guia-de-atendimento/unidades/{location}', [AdminContactGuideController::class, 'destroyLocation'])->whereNumber('location')->name('contact-guide.locations.destroy');
    Route::post('guia-de-atendimento/setores', [AdminContactGuideController::class, 'storeEntry'])->name('contact-guide.entries.store');
    Route::put('guia-de-atendimento/setores/{entry}', [AdminContactGuideController::class, 'updateEntry'])->whereNumber('entry')->name('contact-guide.entries.update');
    Route::delete('guia-de-atendimento/setores/{entry}', [AdminContactGuideController::class, 'destroyEntry'])->whereNumber('entry')->name('contact-guide.entries.destroy');
    Route::get('dhl', [DhlSettingController::class, 'edit'])->name('dhl.edit');
    Route::get('dhl/medidas', [DhlMeasurementRuleController::class, 'index'])->name('dhl.measurements.index');
    Route::put('dhl/medidas/{measurementRule}', [DhlMeasurementRuleController::class, 'update'])->name('dhl.measurements.update');
    Route::post('dhl/medidas/sincronizar', [DhlMeasurementRuleController::class, 'sync'])->name('dhl.measurements.sync');
    Route::get('dhl/localidades/paises', [DhlSettingController::class, 'countries'])->middleware('throttle:30,1')->name('dhl.locations.countries');
    Route::get('dhl/localidades/subdivisoes', [DhlSettingController::class, 'subdivisions'])->middleware('throttle:60,1')->name('dhl.locations.subdivisions');
    Route::get('dhl/localidades/cidades', [DhlSettingController::class, 'cities'])->middleware('throttle:60,1')->name('dhl.locations.cities');
    Route::get('dhl/localidades/codigos-postais', [DhlSettingController::class, 'postalCodes'])->middleware('throttle:60,1')->name('dhl.locations.postal-codes');
    Route::put('dhl', [DhlSettingController::class, 'update'])->name('dhl.update');
    Route::post('dhl/testar', [DhlSettingController::class, 'test'])->name('dhl.test');
    Route::redirect('visao-geral', '/admin')->name('overview');
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
    Route::post('products/feed/generate', [AdminProductFeedController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('products.feed.generate');
    Route::get('products/ai-batches', [ProductAiBatchController::class, 'index'])->name('products.ai-batches.index');
    Route::post('products/ai-batches/preview', [ProductAiBatchController::class, 'preview'])->name('products.ai-batches.preview');
    Route::get('products/ai-batches/catalog-products',[ProductAiBatchController::class, 'catalogProducts'])->name('products.ai-batches.catalog-products');
    Route::get('products/ai-batches/{batch}', [ProductAiBatchController::class, 'show'])->whereNumber('batch')->name('products.ai-batches.show');
    Route::post('products/ai-batches/{batch}/products', [ProductAiBatchController::class, 'addProducts'])->whereNumber('batch')->name('products.ai-batches.products.add');
    Route::post('products/ai-batches/{batch}/dispatch', [ProductAiBatchController::class, 'dispatch'])->whereNumber('batch')->name('products.ai-batches.dispatch');
    Route::get('products/ai-batches/{batch}/status', [ProductAiBatchController::class, 'status'])->whereNumber('batch')->name('products.ai-batches.status');
    Route::post('products/{product}/complete-with-ai', [ProductControllerAdmin::class, 'completeWithAi'])
        ->middleware('throttle:20,1')
        ->name('products.completeWithAi');
    Route::get('sections-home', [AdminHighlightController::class, 'index'])->name('sections_home.index');
    Route::patch('sections-home/update', [AdminHighlightController::class, 'update'])->name('sections_home.update');
    Route::post('currencies', [CurrencyControllerAdmin::class, 'store'])->name('currencies.store');
    // Antes do resource: 'cupons/produtos' não pode cair na rota 'cupons/{cupon}'.
    Route::get('cupons/produtos', [CuponController::class, 'buscarProdutos'])->name('cupons.produtos');
    Route::resource('cupons', CuponController::class);
    Route::patch('cupons/{cupon}/toggle', [CuponController::class, 'toggle'])->name('cupons.toggle');
    Route::get('/produto/{product}', [ProductController::class, 'show'])->name('produto.show');
    Route::get('products/review/pdf', [ProductControllerAdmin::class, 'reviewPdf'])->name('products.review.pdf');
    Route::get('products/review', [ProductControllerAdmin::class, 'review'])->name('products.review');
    Route::get('products/outlet/lote', [ProductControllerAdmin::class, 'outletForm'])->name('products.outlet.form');
    Route::put('products/outlet/lote', [ProductControllerAdmin::class, 'updateOutlet'])->name('products.outlet.update');
    Route::put('currencies/{id}', [CurrencyControllerAdmin::class, 'update'])->name('currencies.update');
    Route::get('currencies/{id}/default', [CurrencyControllerAdmin::class, 'setDefault'])->name('currencies.default');
    Route::patch('products/{product}/update-highlights', [ProductControllerAdmin::class, 'updateHighlights'])->name('products.updateHighlights');
    Route::get('maintenance', [SystemController::class, 'maintenanceIndex'])->name('maintenance.index');
    Route::get('maintenance/toggle', [SystemController::class, 'toggleMaintenance'])->name('maintenance.toggle');
    Route::get('products/subcategories/{category}', [ProductControllerAdmin::class, 'getSubcategories'])->name('products.getSubcategories');
    Route::get('products/categorias-filhas/{subcategory}', [ProductControllerAdmin::class, 'getChildcategories'])->name('products.getcategorias-filhas');
    Route::delete('categorias-filhas/{categorias_filha}/delete-photo', [CategoriasFilhasControllerAdmin::class, 'deletePhoto'])->name('categorias-filhas.deletePhoto');
    Route::post('categorias-filhas/{categorias_filha}/upload-photo', [CategoriasFilhasControllerAdmin::class, 'uploadPhoto'])->name('categorias-filhas.uploadPhoto');
    Route::delete('categorias-filhas/{categorias_filha}/delete-banner', [CategoriasFilhasControllerAdmin::class, 'deleteBanner'])->name('categorias-filhas.deleteBanner');
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
    Route::post('subcategories/{subcategory}/upload-photo', [SubcategoryControllerAdmin::class, 'uploadPhoto'])->name('subcategories.uploadPhoto');
    Route::delete('subcategories/{subcategory}/delete-banner', [SubcategoryControllerAdmin::class, 'deleteBanner'])->name('subcategories.deleteBanner');
    Route::post('subcategories/{subcategory}/upload-banner', [SubcategoryControllerAdmin::class, 'uploadBanner'])->name('subcategories.uploadBanner');
    Route::resource('categories', CategoryControllerAdmin::class);
    Route::delete('categories/{category}/delete-photo', [CategoryControllerAdmin::class, 'deletePhoto'])->name('categories.deletePhoto');
    Route::post('categories/{id}/upload-photo', [CategoryControllerAdmin::class, 'uploadPhoto'])->name('categories.uploadPhoto');
    Route::delete('categories/{category}/delete-banner', [CategoryControllerAdmin::class, 'deleteBanner'])->name('categories.deleteBanner');
    Route::post('categories/{category}/upload-banner', [CategoryControllerAdmin::class, 'uploadBanner'])->name('categories.uploadBanner');
    Route::get('categories/convert-images', [CategoryControllerAdmin::class, 'convertCategoryImagesToWebp'])->name('categories.convertImages');
    Route::resource('orders', OrderController::class)->only(['index', 'show', 'destroy']);
    Route::post('orders/{order}/notes', [OrderController::class, 'storeNote'])->name('orders.notes.store');
    Route::post('orders/{order}/rendix-refund', [OrderController::class, 'refundRendix'])
        ->name('orders.rendix-refund');
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

    foreach (range(1, 9) as $i) {
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
    Route::post('/logocafebistroasuncion/upload', [ImageUploadController::class, 'uploadLogoCafeBistroAsuncion'])->name('logocafebistroasuncion.upload');
    Route::delete('/logocafebistroasuncion/delete', [ImageUploadController::class, 'deleteLogoCafeBistroAsuncion'])->name('logocafebistroasuncion.delete');
    Route::resource('products', ProductControllerAdmin::class);
    Route::delete('products/{id}/photo', [ProductControllerAdmin::class, 'deletePhoto'])->name('products.deletePhoto');
    Route::delete('products/{id}/gallery/{image}', [ProductControllerAdmin::class, 'deleteGalleryImage'])->name('products.deleteGalleryImage');
    Route::resource('brands', BrandControllerAdmin::class);
    Route::delete('brands/{brand}/delete-logo', [BrandControllerAdmin::class, 'deleteLogo'])->name('brands.deleteLogo');
    Route::post('brands/{brand}/upload-logo', [BrandControllerAdmin::class, 'uploadLogo'])->name('brands.uploadLogo');
    Route::delete('brands/{brand}/home-carousel-image', [BrandControllerAdmin::class, 'deleteHomeCarouselImage'])->name('brands.deleteHomeCarouselImage');
    Route::post('brands/{brand}/home-carousel-image', [BrandControllerAdmin::class, 'uploadHomeCarouselImage'])->name('brands.uploadHomeCarouselImage');
    Route::resource('contatos', ContactControllerAdmin::class)->only(['index', 'destroy']);
    Route::get('contatos/export', [ContactControllerAdmin::class, 'export'])->name('contacts.export');
    Route::patch('contatos/{contact}/lido', [ContactControllerAdmin::class, 'read'])->name('contacts.read');
    Route::post('contatos/marcar-todos-lidos', [ContactControllerAdmin::class, 'markAllRead'])->name('contacts.read-all');
    Route::post('contatos/acoes-em-lote', [ContactControllerAdmin::class, 'bulk'])->name('contacts.bulk');
    Route::post('contatos/{contact}/enviar-rh', [ContactControllerAdmin::class, 'sendToHr'])
        ->whereNumber('contact')->name('contacts.send-hr');
    Route::get('contatos/email/novo', [EmailMarketingController::class, 'create'])->name('emails.create');
    Route::post('contatos/email/enviar', [EmailMarketingController::class, 'send'])
        ->middleware('throttle:10,1')->name('emails.send');
    Route::get('contatos/templates/novo', [EmailMarketingController::class, 'templateCreate'])->name('email-templates.create');
    Route::post('contatos/templates', [EmailMarketingController::class, 'templateStore'])->name('email-templates.store');
    Route::get('contatos/templates/{template}/editar', [EmailMarketingController::class, 'templateEdit'])->name('email-templates.edit');
    Route::put('contatos/templates/{template}', [EmailMarketingController::class, 'templateUpdate'])->name('email-templates.update');
    Route::delete('contatos/templates/{template}', [EmailMarketingController::class, 'templateDestroy'])->name('email-templates.destroy');
    Route::get('trabalhe-conosco', [JobFlyerControllerAdmin::class, 'index'])->name('trabalhe_conosco.index');
    Route::post('trabalhe-conosco', [JobFlyerControllerAdmin::class, 'store'])->name('trabalhe_conosco.store');
    Route::post('trabalhe-conosco/{jobFlyer}/toggle', [JobFlyerControllerAdmin::class, 'toggle'])->name('trabalhe_conosco.toggle');
    Route::post('trabalhe-conosco/{jobFlyer}/move/{direction}', [JobFlyerControllerAdmin::class, 'move'])
        ->whereIn('direction', ['up', 'down'])
        ->name('trabalhe_conosco.move');
    Route::delete('trabalhe-conosco/{jobFlyer}', [JobFlyerControllerAdmin::class, 'destroy'])->name('trabalhe_conosco.destroy');
    Route::get('notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');
    Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');
    Route::post('notifications/bulk', [NotificationController::class, 'bulkAction'])
        ->name('notifications.bulk');
    Route::post('notifications/{notification}/archive', [NotificationController::class, 'archive'])
        ->whereNumber('notification')
        ->name('notifications.archive');
    Route::post('notifications/{notification}/restore', [NotificationController::class, 'restore'])
        ->whereNumber('notification')
        ->name('notifications.restore');
    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])
        ->whereNumber('notification')
        ->name('notifications.destroy');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->whereNumber('notification')
        ->name('notifications.read');
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
