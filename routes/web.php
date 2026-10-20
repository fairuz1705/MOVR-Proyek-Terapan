<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AlamatController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RatingTokoController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\TrackingController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\NotificationBuyerController;
use App\Http\Controllers\Admin\NotificationAdminController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\WishlistController as ApiWishlistController;
use App\Http\Controllers\Api\CartController as ApiCartController;
use App\Http\Controllers\Api\ReviewController as ApiReviewController;
use App\Http\Controllers\TryOnController;
use Illuminate\Http\Request;

// Public
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/language/{locale}', function (Request $request, string $locale) {
    if (! in_array($locale, ['id', 'en'], true)) {
        abort(404);
    }

    $request->session()->put('locale', $locale);

    return back();
})->name('language.switch');
Route::get('/kategori/{slug}', [CategoryController::class, 'show'])->name('category.show');
Route::get('/category/{slug}', [CategoryController::class, 'show'])->name('category.show.alias');
Route::get('/category/all', [ProductController::class, 'index'])->name('category.all');
Route::get('/produk/{slug}', [ProductController::class, 'show'])->name('product.show');
Route::get('/product/{slug}', [ProductController::class, 'show'])->name('product.show.alias');
Route::get('/produk', [ProductController::class, 'index'])->name('product.index');
Route::get('/search', [ProductController::class, 'search'])->name('product.search');
Route::post('/voucher/check', [VoucherController::class, 'check'])->name('voucher.check');
Route::get('/voucher', [VoucherController::class, 'index'])->name('voucher.index');
Route::post('/voucher/apply', [VoucherController::class, 'apply'])->name('voucher.apply');

// Auth
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.post');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.post');
    Route::get('/forgot-password', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/reset-password', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])->name('password.update');
});

// Protected (buyer routes)
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');


    // Keranjang
    Route::get('/keranjang', [CartController::class, 'index'])->name('cart.index');
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index.alias');
    Route::post('/keranjang', [CartController::class, 'add'])->name('cart.store');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/keranjang/{id}', [CartController::class, 'remove'])->name('cart.destroy');
    Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');

    // Wishlist
    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::post('/wishlist/add', [WishlistController::class, 'add'])->name('wishlist.add');
    Route::delete('/wishlist/remove', [WishlistController::class, 'remove'])->name('wishlist.remove');

    // Checkout & Payment
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'storeSelection'])->name('checkout.store');
    Route::post('/checkout/process', [CheckoutController::class, 'process'])->name('checkout.process');
    Route::post('/checkout/apply-voucher', [CheckoutController::class, 'applyVoucher'])->name('checkout.apply_voucher');
    Route::post('/voucher/klaim', [VoucherController::class, 'klaim'])->name('voucher.klaim')->middleware(['auth']);

    Route::get('/pay/{kode_transaksi}', [PaymentController::class, 'show'])->name('payment.show');
    Route::post('/payment/{kode}/upload-proof', [PaymentController::class, 'uploadProof'])->name('payment.upload-proof')->middleware(['auth']);
    Route::match(['get', 'post'], '/payment/{kode}/confirm', [PaymentController::class, 'confirm'])->name('payment.confirm')->middleware(['auth']);
    Route::get('/payment/{kode}/waiting', [PaymentController::class, 'waiting'])->name('payment.waiting')->middleware(['auth']);
    Route::get('/payment/{kode}/check-status', [PaymentController::class, 'checkStatus'])->name('payment.check-status')->middleware(['auth']);
    Route::post('/payment/{kode_transaksi}/confirm', [PaymentController::class, 'confirmByBuyer'])->name('payment.confirm.buyer');

    Route::get('/profile/addresses', [ProfileController::class, 'addresses'])->name('profile.addresses');
    Route::get('/profile/alamat/create', [ProfileController::class, 'createAddress'])->name('profile.alamat.create');
    Route::get('/profile/alamat/{id}/edit', [ProfileController::class, 'editAddress'])->name('profile.alamat.edit');
    Route::put('/profile/alamat/{id}/utama', [ProfileController::class, 'setPrimaryAddress'])->name('alamat.utama');

    // Order & Tracking
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{kode_transaksi}', [OrderController::class, 'show'])->name('orders.show')->middleware(['auth']);
    Route::get('/orders/{kode}/json', [OrderController::class, 'showJson'])->name('orders.show.json');
    Route::post('/orders/{kode}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel')->middleware(['auth']);
    Route::post('/orders/{kode}/complete', [OrderController::class, 'complete'])->name('orders.complete')->middleware(['auth']);
    Route::get('/orders/{kode}/cancelled', [OrderController::class, 'cancelled'])->name('orders.cancelled')->middleware(['auth']);
    Route::get('/tracking/{kode_transaksi}', [TrackingController::class, 'show'])->name('tracking.show');
    Route::get('/orders/{kode_transaksi}/tracking', [TrackingController::class, 'show'])->name('order.tracking');

    // Rating for completed orders
    Route::get('/orders/{kode_transaksi}/rating', [RatingController::class, 'show'])->name('orders.rating');
    Route::post('/orders/{kode_transaksi}/rating', [RatingController::class, 'store'])->name('orders.rating.store');

    Route::get('/rating/produk/{produkId}', [RatingController::class, 'form'])->name('order.rating.produk');
    Route::post('/rating/produk/{produkId}', [RatingController::class, 'submit'])->name('rating.product.submit');
    Route::get('/rating/toko/{supplierId}', [RatingTokoController::class, 'form'])->name('rating.toko.form');
    Route::post('/rating/toko/{supplierId}', [RatingTokoController::class, 'submit'])->name('rating.toko.submit');

    // Profile & Alamat
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'updateProfile'])->name('profile.update');
    Route::put('/profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');

    Route::get('/profile/addresses/create', [ProfileController::class, 'createAddress'])->name('profile.address.create');
    Route::get('/profile/address/create', [ProfileController::class, 'createAddress'])->name('profile.address.create.alias');
    Route::post('/profile/addresses', [ProfileController::class, 'storeAddress'])->name('profile.address.store');
    Route::get('/profile/addresses/{id}/edit', [ProfileController::class, 'editAddress'])->name('profile.address.edit');
    Route::put('/profile/addresses/{id}', [ProfileController::class, 'updateAddress'])->name('profile.address.update');
    Route::delete('/profile/addresses/{id}', [ProfileController::class, 'deleteAddress'])->name('profile.address.delete');
    Route::put('/profile/addresses/{id}/set-primary', [ProfileController::class, 'setPrimaryAddress'])->name('profile.address.set-primary');

    Route::get('/profile/payment-methods', [ProfileController::class, 'paymentMethods'])->name('profile.payment-methods');
    Route::post('/profile/payment-methods', [ProfileController::class, 'storePaymentMethod'])->name('profile.payment-methods.store');
    Route::delete('/profile/payment-methods/{id}', [ProfileController::class, 'deletePaymentMethod'])->name('profile.payment-methods.delete');

    Route::get('/notifications/unread', [NotificationBuyerController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/{id}/read', [NotificationBuyerController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationBuyerController::class, 'readAll'])->name('notifications.read-all');
    Route::get('/notifications', [NotificationBuyerController::class, 'index'])->name('notifications.index');

    // AI Virtual Try-On Routes
    Route::prefix('tryon')->name('tryon.')->group(function () {
        Route::post('/upload', [TryOnController::class, 'upload'])->name('upload')->middleware('throttle:10,1');
        Route::get('/products', [TryOnController::class, 'searchProducts'])->name('products');
        Route::post('/generate', [TryOnController::class, 'generate'])->name('generate');
        Route::get('/status/{id}', [TryOnController::class, 'checkStatus'])->name('status');
        Route::get('/result/{id}', [TryOnController::class, 'getResult'])->name('result');
        Route::delete('/photo/{id?}', [TryOnController::class, 'deletePhoto'])->name('delete');
    });
});

// Admin routes (prefix /admin)
Route::middleware(['auth','role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/export', [\App\Http\Controllers\Admin\DashboardExportController::class, 'export'])->name('dashboard.export');
    Route::get('/dashboard/import', function () {
        return redirect()->route('admin.dashboard')->with('info', 'Fitur import belum tersedia.');
    })->name('dashboard.import');

    Route::get('/notifications/unread', [NotificationAdminController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/{id}/read', [NotificationAdminController::class, 'markRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationAdminController::class, 'readAll'])->name('notifications.read-all');

    

    // Master Product (admin)
    Route::get('/master-product', [\App\Http\Controllers\Admin\MasterProductController::class, 'index'])->name('master-product.index');
    Route::get('/master-product/export', [\App\Http\Controllers\Admin\MasterProductController::class, 'export'])->name('master-product.export');
    Route::get('/master-product/events', [\App\Http\Controllers\Admin\MasterProductController::class, 'events'])->name('master-product.events');
    // Create routes MUST come before {id} param routes
    Route::get('/master-product/create', [\App\Http\Controllers\Admin\MasterProductCreateController::class, 'create'])->name('master-product.create');
    Route::post('/master-product', [\App\Http\Controllers\Admin\MasterProductCreateController::class, 'store'])->name('master-product.store');
    Route::get('/master-product/create/variant', [\App\Http\Controllers\Admin\MasterProductCreateController::class, 'createVariant'])->name('master-product.variant.create');
    Route::post('/master-product/variant', [\App\Http\Controllers\Admin\MasterProductCreateController::class, 'storeVariant'])->name('master-product.variant.store');
    Route::get('/master-product/create/media', [\App\Http\Controllers\Admin\MasterProductCreateController::class, 'createMedia'])->name('master-product.media.create');
    Route::post('/master-product/media', [\App\Http\Controllers\Admin\MasterProductCreateController::class, 'storeMedia'])->name('master-product.media.store');
    // Parameterized routes come last
    Route::get('/master-product/{id}', [\App\Http\Controllers\Admin\MasterProductController::class, 'show'])->name('master-product.detail');
    Route::get('/master-product/{id}/edit-confirm', [\App\Http\Controllers\Admin\MasterProductController::class, 'editConfirm'])->name('master-product.edit-confirm');
    Route::get('/master-product/{id}/edit', [\App\Http\Controllers\Admin\MasterProductController::class, 'edit'])->name('master-product.edit');
    Route::put('/master-product/{id}', [\App\Http\Controllers\Admin\MasterProductController::class, 'update'])->name('master-product.update');
    Route::delete('/master-product/{id}', [\App\Http\Controllers\Admin\MasterProductController::class, 'destroy'])->name('master-product.destroy');

    // Category Management
    Route::get('/category', [\App\Http\Controllers\Admin\CategoryController::class, 'index'])->name('category.index');
    Route::get('/category/events', [\App\Http\Controllers\Admin\CategoryController::class, 'events'])->name('category.events');
    Route::post('/category/store', [\App\Http\Controllers\Admin\CategoryController::class, 'store'])->name('category.store');
    Route::put('/category/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'update'])->name('category.update');
    Route::delete('/category/{id}', [\App\Http\Controllers\Admin\CategoryController::class, 'destroy'])->name('category.destroy');

    // Supplier Management
    Route::get('/supplier', [\App\Http\Controllers\Admin\SupplierController::class, 'index'])->name('supplier.index');
    Route::get('/supplier/create', [\App\Http\Controllers\Admin\SupplierController::class, 'create'])->name('supplier.create');
    Route::post('/supplier/store', [\App\Http\Controllers\Admin\SupplierController::class, 'store'])->name('supplier.store');
    Route::get('/supplier/{id}', [\App\Http\Controllers\Admin\SupplierController::class, 'show'])->name('supplier.detail');
    Route::delete('/supplier/{id}', [\App\Http\Controllers\Admin\SupplierController::class, 'destroy'])->name('supplier.destroy');

    // Variant Management
    Route::get('/variant', [\App\Http\Controllers\Admin\VariantController::class, 'index'])->name('variant.index');
    Route::get('/variant/events', [\App\Http\Controllers\Admin\VariantController::class, 'events'])->name('variant.events');
    Route::post('/variant/store', [\App\Http\Controllers\Admin\VariantController::class, 'store'])->name('variant.store');
    Route::put('/variant/{id}', [\App\Http\Controllers\Admin\VariantController::class, 'update'])->name('variant.update');
    Route::delete('/variant/{id}', [\App\Http\Controllers\Admin\VariantController::class, 'destroy'])->name('variant.destroy');

    // Media Management (AD6)
    Route::get('/media', [\App\Http\Controllers\Admin\MediaController::class, 'index'])->name('media.index');
    Route::post('/media/upload', [\App\Http\Controllers\Admin\MediaController::class, 'upload'])->name('media.upload');
    Route::put('/media/{id}/set-thumbnail', [\App\Http\Controllers\Admin\MediaController::class, 'setThumbnail'])->name('media.set-thumbnail');
    Route::delete('/media/{id}', [\App\Http\Controllers\Admin\MediaController::class, 'destroy'])->name('media.destroy');

    // Pricing Management (AD7)
    Route::get('/pricing', [\App\Http\Controllers\Admin\PricingController::class, 'index'])->name('pricing.index');
    Route::put('/pricing/{id}', [\App\Http\Controllers\Admin\PricingController::class, 'update'])->name('pricing.update');
    Route::post('/pricing/bulk-update', [\App\Http\Controllers\Admin\PricingController::class, 'bulkUpdate'])->name('pricing.bulk-update');

    // Supplier Product (AD8)
    Route::get('/supplier-product', [\App\Http\Controllers\Admin\SupplierProductController::class, 'index'])->name('supplier-product.index');
    Route::post('/supplier-product/store', [\App\Http\Controllers\Admin\SupplierProductController::class, 'store'])->name('supplier-product.store');
    Route::put('/supplier-product/{id}', [\App\Http\Controllers\Admin\SupplierProductController::class, 'update'])->name('supplier-product.update');
    Route::delete('/supplier-product/{id}', [\App\Http\Controllers\Admin\SupplierProductController::class, 'destroy'])->name('supplier-product.destroy');

    // Stock Management (AD9)
    Route::get('/stock', [\App\Http\Controllers\Admin\StockController::class, 'index'])->name('stock.index');
    Route::post('/stock/adjust', [\App\Http\Controllers\Admin\StockController::class, 'adjust'])->name('stock.adjust');

    // Stock Movement Log (AD10)
    Route::get('/stock-movement', [\App\Http\Controllers\Admin\StockMovementController::class, 'index'])->name('stock-movement.index');
    Route::get('/stock-movement/export', [\App\Http\Controllers\Admin\StockMovementController::class, 'export'])->name('stock-movement.export');

    // Promotion Management (AD15)
    Route::get('/promotion', [\App\Http\Controllers\Admin\PromotionController::class, 'index'])->name('promotion.index');
    Route::post('/promotion/voucher', [\App\Http\Controllers\Admin\PromotionController::class, 'storeVoucher'])->name('promotion.voucher.store');
    Route::put('/promotion/voucher/{id}', [\App\Http\Controllers\Admin\PromotionController::class, 'updateVoucher'])->name('promotion.voucher.update');
    Route::delete('/promotion/voucher/{id}', [\App\Http\Controllers\Admin\PromotionController::class, 'destroyVoucher'])->name('promotion.voucher.destroy');
    Route::post('/promotion/promo', [\App\Http\Controllers\Admin\PromotionController::class, 'storePromo'])->name('promotion.promo.store');
    Route::put('/promotion/promo/{id}', [\App\Http\Controllers\Admin\PromotionController::class, 'updatePromo'])->name('promotion.promo.update');
    Route::delete('/promotion/promo/{id}', [\App\Http\Controllers\Admin\PromotionController::class, 'destroyPromo'])->name('promotion.promo.destroy');

    // Shipping Management (AD16)
    Route::get('/shipping', [\App\Http\Controllers\Admin\ShippingController::class, 'index'])->name('shipping.index');
    Route::post('/shipping/ekspedisi', [\App\Http\Controllers\Admin\ShippingController::class, 'storeEkspedisi'])->name('shipping.ekspedisi.store');
    Route::put('/shipping/ekspedisi/{id}', [\App\Http\Controllers\Admin\ShippingController::class, 'updateEkspedisi'])->name('shipping.ekspedisi.update');
    Route::delete('/shipping/ekspedisi/{id}', [\App\Http\Controllers\Admin\ShippingController::class, 'destroyEkspedisi'])->name('shipping.ekspedisi.destroy');
    Route::put('/shipping/ekspedisi/{id}/toggle', [\App\Http\Controllers\Admin\ShippingController::class, 'toggleEkspedisi'])->name('shipping.ekspedisi.toggle');
    Route::post('/shipping/{id}/update-resi', [\App\Http\Controllers\Admin\ShippingController::class, 'updateResi'])->name('shipping.update-resi');
    Route::post('/shipping/{id}/update-status', [\App\Http\Controllers\Admin\ShippingController::class, 'updateStatus'])->name('shipping.update-status');
    Route::post('/shipping/tracking-log', [\App\Http\Controllers\Admin\ShippingController::class, 'storeTrackingLog'])->name('shipping.tracking-log.store');

    // Report & Analytics (AD17)
    Route::get('/report', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('report.index');
    Route::get('/report/export', [\App\Http\Controllers\Admin\ReportController::class, 'export'])->name('report.export');

    // Security & Audit Log (AD18)
    Route::get('/audit-log', [\App\Http\Controllers\Admin\AuditLogController::class, 'index'])->name('audit-log.index');

    // Customer Management (AD14)
    Route::get('/customer', [\App\Http\Controllers\Admin\CustomerController::class, 'index'])->name('customer.index');
    Route::put('/customer/{id}/block', [\App\Http\Controllers\Admin\CustomerController::class, 'block'])->name('customer.block');

    // Review & Rating Moderation (AD13)
    Route::get('/review', [\App\Http\Controllers\Admin\ReviewController::class, 'index'])->name('review.index');
    Route::get('/review/{id}', [\App\Http\Controllers\Admin\ReviewController::class, 'show'])->name('review.show');
    Route::delete('/review/{id}', [\App\Http\Controllers\Admin\ReviewController::class, 'destroy'])->name('review.destroy');
    Route::post('/review/{id}/reply', [\App\Http\Controllers\Admin\ReviewController::class, 'reply'])->name('review.reply');

    // Customer Order Management (AD12)
    Route::get('/customer-order', [\App\Http\Controllers\Admin\CustomerOrderController::class, 'index'])->name('customer-order.index');
    Route::get('/customer-order/{kode_transaksi}', [\App\Http\Controllers\Admin\CustomerOrderController::class, 'show'])->name('customer-order.show');
    Route::post('/customer-order/{id}/verify', [\App\Http\Controllers\Admin\CustomerOrderController::class, 'verify'])->name('customer-order.verify');
    Route::post('/customer-order/{id}/reject', [\App\Http\Controllers\Admin\CustomerOrderController::class, 'reject'])->name('customer-order.reject');
    Route::post('/customer-order/{id}/verify-payment', [\App\Http\Controllers\Admin\CustomerOrderController::class, 'verifyPayment'])->name('customer-order.verify-payment');
    Route::post('/customer-order/{id}/update-status', [\App\Http\Controllers\Admin\CustomerOrderController::class, 'updateStatus'])->name('customer-order.update-status');
    Route::post('/customer-order/{id}/update-resi', [\App\Http\Controllers\Admin\CustomerOrderController::class, 'updateResi'])->name('customer-order.update-resi');
    Route::get('/customer-order/{id}/invoice', [\App\Http\Controllers\Admin\CustomerOrderController::class, 'invoicePdf'])->name('customer-order.invoice-pdf');

    // Supplier Order (AD11)
    Route::get('/supplier-order', [\App\Http\Controllers\Admin\SupplierOrderController::class, 'index'])->name('supplier-order.index');
    Route::get('/supplier-order/create', [\App\Http\Controllers\Admin\SupplierOrderController::class, 'create'])->name('supplier-order.create');
    Route::post('/supplier-order', [\App\Http\Controllers\Admin\SupplierOrderController::class, 'store'])->name('supplier-order.store');
    Route::get('/supplier-order/{id}', [\App\Http\Controllers\Admin\SupplierOrderController::class, 'show'])->name('supplier-order.show');
    Route::post('/supplier-order/{id}/receive', [\App\Http\Controllers\Admin\SupplierOrderController::class, 'receive'])->name('supplier-order.receive');
    Route::get('/supplier-order/{id}/invoice', [\App\Http\Controllers\Admin\SupplierOrderController::class, 'invoice'])->name('supplier-order.invoice');
    Route::get('/supplier-order/{id}/invoice-pdf', [\App\Http\Controllers\Admin\SupplierOrderController::class, 'invoicePdf'])->name('supplier-order.invoice-pdf');
});
Route::get('/api/search-suggest', [SearchController::class, 'suggest']);

Route::get('/api/cart-count', function (Request $request) {
    if (!auth()->check()) {
        return response()->json(['count' => 0]);
    }
    $ownerColumn = \App\Models\Keranjang::ownerColumn();
    $ownerId = \App\Models\Keranjang::resolveOwnerId(auth()->user());
    $count = $ownerId
        ? \App\Models\Keranjang::where($ownerColumn, $ownerId)->distinct()->count('detail_produk_id')
        : 0;
    return response()->json(['count' => $count]);
});

Route::get('/api/wishlist-count', function (Request $request) {
    if (!auth()->check()) {
        return response()->json(['count' => 0]);
    }
    $ownerColumn = \App\Models\Wishlist::ownerColumn();
    $ownerId = \App\Models\Wishlist::resolveOwnerId(auth()->user());
    $count = $ownerId ? \App\Models\Wishlist::where($ownerColumn, $ownerId)->count() : 0;
    return response()->json(['count' => $count]);
});

Route::post('/api/wishlist/toggle', [ApiWishlistController::class, 'toggle'])->middleware('auth');

Route::post('/api/cart/add', [ApiCartController::class, 'add'])->middleware('auth');

Route::post('/api/cart/update', [ApiCartController::class, 'update'])->middleware('auth');
Route::delete('/api/cart/remove/{id}', [ApiCartController::class, 'remove'])->middleware('auth');

Route::post('/api/review/store', [ApiReviewController::class, 'store'])->middleware('auth');
Route::post('/review/store', [ApiReviewController::class, 'store'])->middleware('auth')->name('review.store');
