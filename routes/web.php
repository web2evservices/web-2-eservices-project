<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ResetPassController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\MunicipalityController;
use App\Http\Controllers\OfficeController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AdminActivityController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ServiceRequestController;
use App\Http\Controllers\Office\AppointmentController;
use App\Http\Controllers\Office\NotificationController;
use App\Http\Controllers\Office\ServiceRequestController as OfficeServiceRequestController;
use App\Http\Controllers\Office\ServiceCategoryController;
use App\Http\Controllers\Office\ServiceController;
use App\Http\Controllers\Office\OfficeDashboardController;
use App\Http\Controllers\Office\OfficeProfileController;
use App\Http\Controllers\Office\QrCodeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PublicServiceController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\CitizenAppointmentController;
use App\Http\Controllers\OfficeDiscoveryController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/login', [UserController::class, 'LoginView']);
Route::post('/login', [UserController::class, 'Login'])->name('login');

Route::post('/create', [UserController::class, 'create']);

Route::get('/otp-verify',  [UserController::class, 'otpView']);
Route::post('/otp-verify', [UserController::class, 'otpVerify']);
Route::post('/otp-resend', [UserController::class, 'otpResend']);
Route::post('/logout', [UserController::class, 'logout'])->name('logout');
Route::get('/forget-password', [ResetPassController::class, 'forgotView']);
Route::post('/forget-password', [ResetPassController::class, 'sendResetLink']);

Route::get('/reset-password/{token}', [ResetPassController::class, 'resetView'])
->name('password.reset');

Route::post('/reset-password', [ResetPassController::class, 'resetPassword'])
->name('password.update');

Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('oauth.callback');


Route::get('/services', [PublicServiceController::class, 'index'])->name('services.index');
Route::get('/services/{id}', [PublicServiceController::class, 'show'])->name('services.show');
Route::get('/api/services/{id}', [PublicServiceController::class, 'apiShow'])->name('api.services.show');
Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('oauth.redirect');

Route::prefix('admin')->middleware(['auth', 'admin'])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index'])
        ->name('admin.dashboard');
    Route::resource('municipalities', MunicipalityController::class);
    Route::resource('offices', OfficeController::class);
    Route::get('users', [AdminUserController::class, 'index'])
        ->name('admin.users.index');
    Route::patch('users/{id}/toggle', [AdminUserController::class, 'toggle'])
        ->name('admin.users.toggle');
    Route::patch('users/{id}/role', [UserController::class, 'updateRole'])
    ->name('admin.users.role');
    Route::delete('/users/{id}/delete', [UserController::class, 'destroy'])
        ->name('admin.users.delete');
    Route::get('analytics', [AnalyticsController::class, 'index'])
        ->name('admin.analytics');
    Route::get('activity', [AdminActivityController::class, 'index'])
        ->name('admin.activity');
});
Route::middleware(['auth'])->group(function () {

    Route::get('/user/dashboard', [UserController::class, 'dashboard'])
        ->name('user.dashboard');

    Route::get('/user-account',            [UserController::class, 'accountView'])  ->name('user.account');
    
    Route::put('/user-account',            [UserController::class, 'updateProfile'])->name('user.account.update');
    
    Route::post('/user-account/toggle-2fa',[UserController::class, 'toggle2FA'])   ->name('user.account.toggle2fa');

    Route::get('/user/requests', [ServiceRequestController::class, 'pageIndex'])
        ->name('user.requests.index');

    Route::get('/user/requests/create', [ServiceRequestController::class, 'pageCreate'])
        ->name('user.requests.create');

    Route::get('/user/requests/{id}', [ServiceRequestController::class, 'pageShow'])
        ->name('user.requests.show');

    Route::get('/user/requests/data', [ServiceRequestController::class, 'index'])
        ->name('user.requests.data');

    Route::get('/user/requests/{id}/data', [ServiceRequestController::class, 'show'])
        ->name('user.requests.data.show');

    Route::post('/user/requests', [ServiceRequestController::class, 'store'])
        ->name('user.requests.store');

    Route::post('/user/requests/{id}/documents', [DocumentController::class, 'store'])
        ->name('user.requests.documents.store');

    Route::get('/user/requests/{id}/payment', [PaymentController::class, 'create'])->name('user.requests.payment.create');
    Route::post('/user/requests/{id}/payment', [PaymentController::class, 'store'])->name('user.requests.payment.store');
    Route::get('/user/requests/{id}/payment/receipt', [PaymentController::class, 'show'])->name('user.requests.payment.show');

    Route::post('/user/requests/{id}/pdf', [ServiceRequestController::class, 'generatePdf'])
        ->name('user.requests.pdf');

    Route::get('/user/requests/{requestId}/documents/{documentId}/download', function ($requestId, $documentId) {
        $request = \App\Models\ServiceRequests::where('id', $requestId)
            ->where('citizen_id', Auth::id() ?? 1)
            ->firstOrFail();

        $document = \App\Models\Documents::where('id', $documentId)
            ->where('service_request_id', $request->id)
            ->firstOrFail();

        $filePath = storage_path('app/public/' . $document->file_path);
        return response()->download($filePath);
    })->name('user.requests.documents.download');

    
    Route::post('/requests/{serviceRequestId}/feedback', [\App\Http\Controllers\FeedbackController::class, 'store'])
        ->name('requests.feedback.store');
    Route::get('/requests/{serviceRequestId}/feedback', [\App\Http\Controllers\FeedbackController::class, 'show'])
        ->name('requests.feedback.show');

    
    Route::get('/messages', [\App\Http\Controllers\MessagesController::class, 'index'])
        ->name('messages.index');
    Route::post('/messages', [\App\Http\Controllers\MessagesController::class, 'store'])
        ->name('messages.store');
    Route::get('/messages/{id}', [\App\Http\Controllers\MessagesController::class, 'show'])
        ->name('messages.show');
    Route::delete('/messages/{id}', [\App\Http\Controllers\MessagesController::class, 'destroy'])
        ->name('messages.destroy');
    Route::get('/messages/{messageId}/attachment', [\App\Http\Controllers\MessagesController::class, 'downloadAttachment'])
        ->name('messages.attachment.download');

    
    Route::get('/user/notifications',                [\App\Http\Controllers\User\NotificationController::class, 'index'])       ->name('user.notifications.index');
    Route::get('/user/notifications/count',          [\App\Http\Controllers\User\NotificationController::class, 'unreadCount']) ->name('user.notifications.count');
    Route::patch('/user/notifications/mark-all-read',[\App\Http\Controllers\User\NotificationController::class, 'markAllRead'])->name('user.notifications.mark-all-read');
    Route::patch('/user/notifications/{id}/read',    [\App\Http\Controllers\User\NotificationController::class, 'markRead'])    ->name('user.notifications.mark-read');
});

Route::post('/payments/stripe/webhook', [PaymentController::class, 'stripeWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/payments/coinbase/webhook', [PaymentController::class, 'coinbaseWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);





Route::prefix('office')->middleware(['auth', 'office'])->name('office.')->group(function () {

    
    Route::get('/dashboard', [OfficeDashboardController::class, 'index'])
        ->name('dashboard');

    
    Route::get('/profile/edit', [OfficeProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::put('/profile', [OfficeProfileController::class, 'update'])
        ->name('profile.update');

    
    Route::get('/categories',          [ServiceCategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create',   [ServiceCategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories',         [ServiceCategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit',[ServiceCategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}',     [ServiceCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}',  [ServiceCategoryController::class, 'destroy'])->name('categories.destroy');

    
    Route::get('/services',          [ServiceController::class, 'index'])->name('services.index');
    Route::get('/services/create',   [ServiceController::class, 'create'])->name('services.create');
    Route::post('/services',         [ServiceController::class, 'store'])->name('services.store');
    Route::get('/services/{id}/edit',[ServiceController::class, 'edit'])->name('services.edit');
    Route::put('/services/{id}',     [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{id}',  [ServiceController::class, 'destroy'])->name('services.destroy');

    
    Route::get('/qr/{requestId}',          [QrCodeController::class, 'show'])->name('qr.show');
    Route::get('/qr/{requestId}/download', [QrCodeController::class, 'download'])->name('qr.download');

    Route::post('/requests/{id}/summary', [OfficeServiceRequestController::class, 'generateSummary'])
        ->name('requests.generate-summary');

    
    Route::get('/requests', [OfficeServiceRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/{id}', [OfficeServiceRequestController::class, 'show'])->name('requests.show');
    Route::patch('/requests/{id}/status', [OfficeServiceRequestController::class, 'updateStatus'])->name('requests.update-status');
    Route::post('/requests/{id}/documents', [OfficeServiceRequestController::class, 'uploadDocument'])->name('requests.upload-document');
    Route::get('/requests/{requestId}/documents/{documentId}/download', [OfficeServiceRequestController::class, 'downloadDocument'])->name('requests.download-document');

    

    Route::resource('appointments', AppointmentController::class)->names('appointments');

    
    Route::get('/notifications',                [NotificationController::class, 'index'])       ->name('notifications.index');
    Route::get('/notifications/count',          [NotificationController::class, 'unreadCount']) ->name('notifications.count');
    Route::patch('/notifications/mark-all-read',[NotificationController::class, 'markAllRead'])->name('notifications.mark-all-read');
    Route::patch('/notifications/{id}/read',    [NotificationController::class, 'markRead'])    ->name('notifications.mark-read');
});


Route::get('/track/{qrCode}', function ($qrCode) {
    $request = \App\Models\ServiceRequests::where('qr_code', $qrCode)
        ->with(['service.office', 'citizen'])
        ->firstOrFail();
    return view('office.public.track', compact('request'));
})->name('requests.track');


Route::get('/demo/payment', [App\Http\Controllers\DemoPaymentController::class, 'index'])->name('demo.payment');
Route::post('/demo/payments/stripe/confirm', [App\Http\Controllers\DemoPaymentController::class, 'confirmStripe']);
Route::post('/demo/payments/crypto/initiate', [App\Http\Controllers\DemoPaymentController::class, 'initiateCrypto']);
Route::get('/demo/payments/receipt', [App\Http\Controllers\DemoPaymentController::class, 'receipt'])->name('demo.payments.receipt');


Route::middleware(['auth', 'office'])->group(function () {
    Route::get('/feedback', [FeedbackController::class, 'officeIndex']);
    Route::post('/feedback/{id}/respond', [FeedbackController::class, 'respond']);
});


Route::get('/offices/map', [OfficeDiscoveryController::class, 'index'])->name('offices.map');
Route::get('/api/offices', [OfficeDiscoveryController::class, 'apiOffices'])->name('api.offices');


Route::post('/webhooks/tap',         [PaymentController::class, 'tapWebhook'])->name('payments.tap.webhook');
Route::post('/webhooks/nowpayments', [PaymentController::class, 'nowPaymentsWebhook'])->name('payments.nowpayments.webhook');


Route::middleware(['auth'])->group(function () {

    
    Route::get('/payments/{id}/crypto/estimate',      [PaymentController::class, 'cryptoEstimate'])->name('payments.crypto.estimate');

    
    Route::post('/payments/{id}/stripe/confirm',      [PaymentController::class, 'confirmStripe'])->name('payments.stripe.confirm');

    
    Route::post('/payments/{id}/tap/initiate',        [PaymentController::class, 'initiateTap'])->name('payments.tap.initiate');
    Route::get('/payments/{id}/tap/callback',         [PaymentController::class, 'tapCallback'])->name('payments.tap.callback');

    
    Route::post('/payments/{id}/crypto/initiate',     [PaymentController::class, 'initiateCrypto'])->name('payments.nowpayments.initiate');
    Route::get('/payments/{id}/crypto/success',       [PaymentController::class, 'nowPaymentsSuccess'])->name('payments.nowpayments.success');

    
    Route::get('/chat',              [ChatController::class, 'index'])->name('user.chat.index');
    Route::get('/chat/{userId}',     [ChatController::class, 'show'])->name('user.chat.show');
    Route::post('/chat/send',        [ChatController::class, 'send'])->name('chat.send');

    
    Route::post('/user/requests/{id}/feedback', [FeedbackController::class, 'store'])->name('user.feedback.store');

    
    Route::prefix('user/appointments')->name('user.appointments.')->group(function () {
        Route::get('/',         [CitizenAppointmentController::class, 'index'])->name('index');
        Route::get('/create',   [CitizenAppointmentController::class, 'create'])->name('create');
        Route::post('/',        [CitizenAppointmentController::class, 'store'])->name('store');
        Route::delete('/{id}',  [CitizenAppointmentController::class, 'destroy'])->name('destroy');
    });
});


Route::middleware(['auth', 'office'])->prefix('office')->name('office.')->group(function () {
    Route::get('/chat',           [ChatController::class, 'officeIndex'])->name('chat.index');
    Route::get('/chat/{userId}',  [ChatController::class, 'officeShow'])->name('chat.show');
    Route::post('/chat/send',     [ChatController::class, 'send'])->name('chat.send');
    Route::get('/feedback',       [FeedbackController::class, 'officeIndex'])->name('feedback.index');
    Route::post('/feedback/{id}/respond', [FeedbackController::class, 'respond'])->name('feedback.respond');
});