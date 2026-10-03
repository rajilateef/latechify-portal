<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CampController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MonnifyWebhookController;
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

// RETIRED (2026-10-01): the old Paystack application flow was replaced by Checkout.
// Kept as a permanent redirect so existing links, bookmarks and indexed URLs still land somewhere useful.
Route::get('/apply', function (\Illuminate\Http\Request $request) {
    $course = \App\Models\Course::where('slug', $request->query('course'))->first();

    return redirect()->to($course ? $course->checkoutUrl() : route('checkout'), 301);
})->name('apply');

// Summer Coding Camp
Route::get('/summer-coding-camp', [CampController::class, 'index'])->name('camp.index');
Route::get('/summer-coding-camp/payment/callback', [CampController::class, 'paymentCallback'])->name('camp.payment.callback');
Route::post('/summer-coding-camp/payment/webhook', MonnifyWebhookController::class)->middleware('throttle:120,1')->name('camp.webhook');
// Bound by uuid (not sequential id) to prevent PII enumeration.
Route::get('/summer-coding-camp/manual/{registration:uuid}', [CampController::class, 'manual'])->name('camp.manual');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

// Legal pages (terms / privacy / cookies) driven by the Page model
Route::get('/terms', [SiteController::class, 'legal'])->defaults('slug', 'terms')->name('terms');
Route::get('/privacy', [SiteController::class, 'legal'])->defaults('slug', 'privacy')->name('privacy');
Route::get('/cookies', [SiteController::class, 'legal'])->defaults('slug', 'cookies')->name('cookies');

// Legacy bank-transfer instructions for applications taken before Checkout replaced the
// apply flow. No new applications can be created; this only serves existing records.
Route::get('/payment/bank-transfer/{application}', [PaymentController::class, 'bankTransfer'])->name('payment.bank-transfer');

// ── Monnify webhook ──
// Monnify allows ONE transaction-completion URL per account, so a single endpoint
// serves checkout, portal fee top-ups and camp registrations (dispatched on the
// LATECHIFY- reference). Register this URL in the Monnify dashboard:
//     https://<your-domain>/webhooks/monnify
// Open by design (no signature gate) — every payment is re-verified with Monnify
// before anything is marked paid; the throttle just blunts log flooding.
Route::post('/webhooks/monnify', MonnifyWebhookController::class)
    ->middleware('throttle:120,1')
    ->name('webhooks.monnify');

// ── Checkout (Monnify) — buy a course; the portal is created once a super admin confirms ──
Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout');
Route::get('/checkout/callback', [CheckoutController::class, 'callback'])->name('checkout.callback');
// Legacy webhook URLs — same handler, kept so an already-registered URL still works.
Route::post('/checkout/webhook', MonnifyWebhookController::class)->middleware('throttle:120,1')->name('checkout.webhook');
// Bound by uuid (not sequential id) to prevent PII enumeration.
Route::get('/checkout/{order:uuid}/transfer', [CheckoutController::class, 'transfer'])->name('checkout.transfer');
Route::get('/checkout/{order:uuid}/status', [CheckoutController::class, 'status'])->name('checkout.status');

// Advert click tracking (increments the counter then redirects out)
Route::get('/go/advert/{advert}', [SiteController::class, 'advertClick'])->name('advert.click');

// ── Trainee / student portal ──
Route::prefix('portal')->name('portal.')->group(function () {
    Route::get('login', [\App\Http\Controllers\Portal\AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [\App\Http\Controllers\Portal\AuthController::class, 'login'])->name('login.attempt');
    Route::post('logout', [\App\Http\Controllers\Portal\AuthController::class, 'logout'])->name('logout');

    Route::middleware('active.student')->group(function () {
        Route::get('/', [\App\Http\Controllers\Portal\PortalController::class, 'dashboard'])->name('dashboard');
        Route::get('announcements', [\App\Http\Controllers\Portal\PortalController::class, 'announcements'])->name('announcements');
        Route::get('schedule', [\App\Http\Controllers\Portal\PortalController::class, 'schedule'])->name('schedule');
        Route::get('fees', [\App\Http\Controllers\Portal\PortalController::class, 'fees'])->name('fees');
        Route::get('certificates', [\App\Http\Controllers\Portal\PortalController::class, 'certificates'])->name('certificates');
        Route::get('enroll', [\App\Http\Controllers\Portal\PortalController::class, 'enroll'])->name('enroll');
        Route::post('enroll', [\App\Http\Controllers\Portal\PortalController::class, 'enrollStore'])->name('enroll.store');
        Route::get('program/{program}', [\App\Http\Controllers\Portal\PortalController::class, 'program'])->name('program');
        Route::get('course/{course}', [\App\Http\Controllers\Portal\PortalController::class, 'course'])->name('course');
        Route::get('class/{class}', [\App\Http\Controllers\Portal\PortalController::class, 'classShow'])->name('class');

        // Assignments
        Route::get('assignments', [\App\Http\Controllers\Portal\AssignmentController::class, 'index'])->name('assignments');
        Route::get('assignments/{assignment}', [\App\Http\Controllers\Portal\AssignmentController::class, 'show'])->name('assignments.show');
        Route::post('assignments/{assignment}/submit', [\App\Http\Controllers\Portal\AssignmentController::class, 'submit'])->name('assignments.submit');

        // Profile
        Route::get('profile', [\App\Http\Controllers\Portal\ProfileController::class, 'edit'])->name('profile');
        Route::put('profile', [\App\Http\Controllers\Portal\ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [\App\Http\Controllers\Portal\ProfileController::class, 'updatePassword'])->name('profile.password');

        // Notifications
        Route::get('notifications', [\App\Http\Controllers\Portal\NotificationController::class, 'index'])->name('notifications');
        Route::get('notifications/{id}/read', [\App\Http\Controllers\Portal\NotificationController::class, 'read'])->name('notifications.read');
        Route::post('notifications/read-all', [\App\Http\Controllers\Portal\NotificationController::class, 'readAll'])->name('notifications.read-all');

        // Materials hub — every slide, document and link the trainee can reach
        Route::get('materials', [\App\Http\Controllers\Portal\MaterialController::class, 'index'])->name('materials');

        // Permission-gated downloads
        Route::get('materials/{material}/download', [\App\Http\Controllers\Portal\MaterialController::class, 'download'])->name('materials.download');
        Route::get('resources/{resource}/open', [\App\Http\Controllers\Portal\MaterialController::class, 'openResource'])->name('resources.open');

        // Pay an outstanding balance online — Monnify, verified via the shared checkout callback/webhook.
        Route::post('fees/{enrollment}/pay', [\App\Http\Controllers\Portal\FeePaymentController::class, 'pay'])->name('fees.pay');
    });
});

// Admin-only download of a course material (files live on the private disk).
Route::get('manage/materials/{material}/download', function (\App\Models\CourseMaterial $material) {
    abort_unless(auth()->check() && auth()->user()->is_admin, 403);
    $disk = \Illuminate\Support\Facades\Storage::disk($material->effectiveDisk());
    $path = $material->effectivePath();
    abort_unless($path && $disk->exists($path), 404);

    return $disk->download($path, $material->effectiveFileName());
})->middleware(['web', 'auth'])->name('admin.materials.download');

// Admin-only download of a class slide/resource (private-disk uploads need a gated route).
Route::get('manage/class-resources/{resource}/download', function (\App\Models\ClassResource $resource) {
    abort_unless(auth()->check() && auth()->user()->is_admin, 403);
    $disk = \Illuminate\Support\Facades\Storage::disk($resource->disk ?: 'public');
    abort_unless($resource->file_path && $disk->exists($resource->file_path), 404);

    return $disk->download($resource->file_path, $resource->file_name ?: basename($resource->file_path));
})->middleware(['web', 'auth'])->name('admin.class-resources.download');

// Admin-only download of a library file.
Route::get('manage/library/{file}/download', function (\App\Models\MaterialFile $file) {
    abort_unless(auth()->check() && auth()->user()->is_admin, 403);
    $disk = \Illuminate\Support\Facades\Storage::disk($file->disk ?: 'local');
    abort_unless($file->file_path && $disk->exists($file->file_path), 404);

    return $disk->download($file->file_path, $file->file_name ?: basename($file->file_path));
})->middleware(['web', 'auth'])->name('admin.library.download');

// Receipts — HTML preview + PDF download (student owner or super admin; authorised in the controller).
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('receipts/{receipt}', [\App\Http\Controllers\ReceiptController::class, 'show'])->name('receipts.show');
    Route::get('receipts/{receipt}/download', [\App\Http\Controllers\ReceiptController::class, 'download'])->name('receipts.download');
});
