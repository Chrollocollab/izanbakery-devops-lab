<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocaleController;
use App\Http\Controllers\MyOrderController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderTrackController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\StatistikController;
use App\Http\Middleware\AuthAdmin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes(['verify' => true]);

// 'facebook' temporarily disabled: requires SSM for client secret. Re-add once SSM is set up.
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->whereIn('provider', ['google'])->name('auth.social.redirect');
Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->whereIn('provider', ['google'])->name('auth.social.callback');

Route::get('/lang/{locale}', [LocaleController::class, 'switch'])->name('lang.switch');

Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');
Route::view('/data-deletion', 'data-deletion')->name('data-deletion');

Route::get('/', [HomeController::class, 'index'])->name('home.index');
Route::get('/infaq', [HomeController::class, 'infaq'])->name('home.infaq');
Route::post('/infaq/contribute', [HomeController::class, 'infaqContribute'])->name('home.infaq.contribute');
Route::get('/shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('/shop/{product_slug}', [ShopController::class, 'product_details'])->name('shop.product.details');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add_to_cart'])->name('cart.add');
Route::put('/cart/update/{rowId}', [CartController::class, 'update_cart'])->name('cart.update');
Route::delete('/cart/remove/{rowId}', [CartController::class, 'remove_item'])->name('cart.remove');
Route::get('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
Route::post('/checkout', [CartController::class, 'place_order'])->name('cart.place-order');
Route::get('/order/confirmation/{id}', [CartController::class, 'order_confirmation'])->name('cart.order.confirmation');

// Guest order tracking (order number + email lookup, no login required)
Route::get('/track-order', [OrderTrackController::class, 'show'])->name('order.track');
Route::post('/track-order', [OrderTrackController::class, 'find'])->name('order.track.find');

Route::middleware(['auth', 'verified'])->group(function () {
    // Account routes
    Route::get('/account/profile', [ProfileController::class, 'edit'])->name('user.profile');
    Route::put('/account/profile', [ProfileController::class, 'update'])->name('user.profile.update');
    Route::get('/account/password', [ProfileController::class, 'editPassword'])->name('user.password');
    Route::put('/account/password', [ProfileController::class, 'updatePassword'])->name('user.password.update');

    // My Orders routes
    Route::get('/account/orders', [MyOrderController::class, 'index'])->name('user.orders');
    Route::get('/account/orders/{order}', [MyOrderController::class, 'show'])->name('user.orders.show');
    Route::get('/account/orders/{order}/receipt', [MyOrderController::class, 'receipt'])->name('user.orders.receipt');

    // Customer Feedback routes
    Route::get('/account/feedback', [FeedbackController::class, 'index'])->name('user.feedback');
    Route::post('/account/feedback', [FeedbackController::class, 'store'])->name('user.feedback.store');
    Route::get('/account/feedback/{feedback}', [FeedbackController::class, 'show'])->name('user.feedback.show');
    Route::delete('/account/feedback/{feedback}', [FeedbackController::class, 'destroy'])->name('user.feedback.destroy');
});

Route::middleware(['auth', AuthAdmin::class])->group(function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
    Route::get('/admin/search', [AdminController::class, 'search'])->name('admin.search');

    // Bell notifications
    Route::get('/admin/notifications/{id}', [AdminNotificationController::class, 'show'])->name('admin.notifications.show');
    Route::post('/admin/notifications/mark-all-read', [AdminNotificationController::class, 'markAllRead'])->name('admin.notifications.mark-all-read');

    // Statistics routes
    Route::get('/admin/statistik', [StatistikController::class, 'index'])->name('admin.statistik');
    Route::get('/admin/statistik/chart-data', [StatistikController::class, 'getChartData'])->name('admin.statistik.chart');

    Route::get('/admin/brands', [AdminController::class, 'brands'])->name('admin.brands');
    Route::get('/admin/brand/add', [AdminController::class, 'add_brand'])->name('admin.brand.add');
    Route::post('/admin/brand/store', [AdminController::class, 'brand_store'])->name('admin.brand.store');
    Route::get('/admin/brandedit/{id}', [AdminController::class, 'brand_edit'])->name('admin.brand.edit');
    Route::put('/admin/brand/update', [AdminController::class, 'brand_update'])->name('admin.brand.update');
    Route::delete('/admin/brand/{id}/delete', [AdminController::class, 'brand_delete'])->name('admin.brand.delete');

    Route::get('/admin/categories', [AdminController::class, 'categories'])->name('admin.categories');
    Route::get('/admin/category/add', [AdminController::class, 'category_add'])->name('admin.category.add');
    Route::post('/admin/category/store', [AdminController::class, 'category_store'])->name('admin.category.store');
    Route::get('/admin/category/{id}/edit', [AdminController::class, 'category_edit'])->name('admin.category.edit');
    Route::put('/admin/category/update', [AdminController::class, 'category_update'])->name('admin.category.update');
    Route::delete('/admin/category/{id}/delete', [AdminController::class, 'category_delete'])->name('admin.category.delete');

    Route::get('/admin/products', [AdminController::class, 'products'])->name('admin.products');
    Route::get('/admin/products/add', [AdminController::class, 'product_add'])->name('admin.product.add');
    Route::post('/admin/products/store', [AdminController::class, 'product_store'])->name('admin.product.store');
    Route::get('/admin/product/{id}/edit', [AdminController::class, 'product_edit'])->name('admin.product.edit');
    Route::put('/admin/product/update', [AdminController::class, 'product_update'])->name('admin.product.update');
    Route::delete('/admin/product/{id}/delete', [AdminController::class, 'product_delete'])->name('admin.product.delete');
    Route::put('/admin/product/{id}/toggle-visibility', [ShopController::class, 'toggle_visibility'])->name('admin.product.toggle');

    // Order Management
    Route::get('/admin/orders', [OrderController::class, 'index'])->name('admin.orders');
    Route::get('/admin/order/create', [OrderController::class, 'create'])->name('admin.order.create');
    Route::post('/admin/order/store', [OrderController::class, 'store'])->name('admin.order.store');
    Route::get('/admin/order/{id}', [OrderController::class, 'show'])->name('admin.order.show');
    Route::get('/admin/order/{id}/receipt', [OrderController::class, 'downloadReceipt'])->name('admin.order.receipt');
    Route::put('/admin/order/{id}/status', [OrderController::class, 'updateStatus'])->name('admin.order.status');
    Route::put('/admin/order/{id}/payment-done', [OrderController::class, 'markPaymentDone'])->name('admin.order.payment-done');
    Route::delete('/admin/order/{id}/delete', [OrderController::class, 'destroy'])->name('admin.order.delete');

    // Infaq settings
    Route::get('/admin/infaq', [AdminController::class, 'infaq'])->name('admin.infaq');
    Route::post('/admin/infaq', [AdminController::class, 'infaq_update'])->name('admin.infaq.update');
    Route::post('/admin/infaq/photo', [AdminController::class, 'infaq_photo_upload'])->name('admin.infaq.photo.upload');
    Route::delete('/admin/infaq/photo/{photo}', [AdminController::class, 'infaq_photo_delete'])->name('admin.infaq.photo.delete');
    Route::post('/admin/infaq/contribution/manual', [AdminController::class, 'infaq_contribution_manual'])->name('admin.infaq.contribution.manual');
    Route::put('/admin/infaq/contribution/{contribution}/verify', [AdminController::class, 'infaq_contribution_verify'])->name('admin.infaq.contribution.verify');
    Route::put('/admin/infaq/contribution/{contribution}/reject', [AdminController::class, 'infaq_contribution_reject'])->name('admin.infaq.contribution.reject');
});
