<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StorefrontController;
use Illuminate\Support\Facades\Route;

Route::match(['get', 'post'], '/', [StorefrontController::class, 'home'])->name('home');
Route::match(['get', 'post'], '/index.php', [StorefrontController::class, 'home']);
Route::get('/Shop.php', [StorefrontController::class, 'shop'])->name('shop');
Route::post('/Shop.php', [StorefrontController::class, 'addToCart'])->name('cart.add');
Route::get('/Checkout.php', [StorefrontController::class, 'checkout'])->name('checkout');
Route::post('/Checkout.php', [StorefrontController::class, 'placeOrder'])->name('checkout.place');
Route::get('/OrderSuccess.php', [StorefrontController::class, 'orderSuccess'])->name('order.success');
Route::get('/FAQ.php', [StorefrontController::class, 'faq'])->name('faq');
Route::match(['get', 'post'], '/OnlineBanking.php', [StorefrontController::class, 'onlineBanking'])->name('online-banking');

Route::get('/Login.php', [AuthController::class, 'showLogin'])->name('login');
Route::post('/Login.php', [AuthController::class, 'login'])->name('login.submit');
Route::get('/Signup.php', [AuthController::class, 'showSignup'])->name('signup');
Route::post('/Signup.php', [AuthController::class, 'register'])->name('signup.submit');
Route::match(['get', 'post'], '/LoginSignup.php', [AuthController::class, 'legacyLoginSignup'])->name('login-signup.legacy');
Route::get('/actions/logout.php', [AuthController::class, 'logout'])->name('logout');
Route::get('/Profile.php', [AuthController::class, 'profile'])->name('profile');
Route::post('/Profile.php', [AuthController::class, 'changePassword'])->name('profile.password');
Route::match(['get', 'post'], '/forgot_password.php', [AuthController::class, 'forgotPassword'])->name('password.forgot');
Route::match(['get', 'post'], '/reset_password.php', [AuthController::class, 'resetPassword'])->name('password.reset');

Route::get('/AdminHome.php', [AdminController::class, 'products'])->name('admin.products');
Route::post('/actions/add_product.php', [AdminController::class, 'addProduct'])->name('admin.products.add');
Route::post('/actions/update_product.php', [AdminController::class, 'updateProduct'])->name('admin.products.update');
Route::post('/actions/delete_product.php', [AdminController::class, 'deleteProduct'])->name('admin.products.delete');
Route::match(['get', 'post'], '/AdminOrder.php', [AdminController::class, 'orders'])->name('admin.orders');
Route::get('/AdminDashboard.php', [AdminController::class, 'dashboard'])->name('admin.dashboard');
Route::get('/AdminComment.php', [AdminController::class, 'comments'])->name('admin.comments');
Route::get('/AdminFAQ.php', [AdminController::class, 'faq'])->name('admin.faq');
