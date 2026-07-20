<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CampController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SiteController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/about', [SiteController::class, 'about'])->name('about');
Route::get('/services', [SiteController::class, 'services'])->name('services');
Route::get('/pricing', [SiteController::class, 'pricing'])->name('pricing');
Route::get('/contact', [SiteController::class, 'contact'])->name('contact');
Route::get('/schedule-consultation', [SiteController::class, 'consultation'])->name('consultation');
Route::get('/verify-certificate', [SiteController::class, 'verifyCertificate'])->name('verify-certificate');

Route::get('/courses', [CourseController::class, 'index'])->name('courses.index');
Route::get('/courses/{course:slug}', [CourseController::class, 'show'])->name('courses.show');

Route::get('/apply', [SiteController::class, 'apply'])->name('apply');

// Summer Coding Camp
Route::get('/summer-coding-camp', [CampController::class, 'index'])->name('camp.index');
Route::get('/summer-coding-camp/payment/callback', [CampController::class, 'paymentCallback'])->name('camp.payment.callback');
Route::post('/summer-coding-camp/payment/webhook', [CampController::class, 'webhook'])->name('camp.webhook');
// Bound by uuid (not sequential id) to prevent PII enumeration.
Route::get('/summer-coding-camp/manual/{registration:uuid}', [CampController::class, 'manual'])->name('camp.manual');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

// Legal pages (terms / privacy / cookies) driven by the Page model
Route::get('/terms', [SiteController::class, 'legal'])->defaults('slug', 'terms')->name('terms');
Route::get('/privacy', [SiteController::class, 'legal'])->defaults('slug', 'privacy')->name('privacy');
Route::get('/cookies', [SiteController::class, 'legal'])->defaults('slug', 'cookies')->name('cookies');

// Payment
Route::get('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
Route::get('/payment/bank-transfer/{application}', [PaymentController::class, 'bankTransfer'])->name('payment.bank-transfer');

// Advert click tracking (increments the counter then redirects out)
Route::get('/go/advert/{advert}', [SiteController::class, 'advertClick'])->name('advert.click');
