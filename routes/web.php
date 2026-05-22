<?php

use App\Http\Controllers\Admin\DoctorController as AdminDoctorController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Doctor\MedicalNoteController;
use App\Http\Controllers\Patient\BookingController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/services', [PublicController::class, 'services'])->name('services.index');
Route::get('/doctors', [PublicController::class, 'doctors'])->name('doctors.index');
Route::get('/booking/slots', [PublicController::class, 'slots'])->name('booking.slots');

Route::get('/booking/create', [BookingController::class, 'create'])->name('booking.create');
Route::post('/payments/midtrans/callback', PaymentWebhookController::class)->name('payments.callback');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profile/two-factor', [ProfileController::class, 'twoFactorSetup'])->name('profile.two-factor.setup');
    Route::post('/profile/two-factor', [ProfileController::class, 'enableTwoFactor'])->name('profile.two-factor.enable');
    Route::post('/profile/two-factor/recovery-codes', [ProfileController::class, 'regenerateRecoveryCodes'])->name('profile.two-factor.recovery-codes');
    Route::delete('/profile/two-factor', [ProfileController::class, 'disableTwoFactor'])->name('profile.two-factor.disable');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/history/{booking}/invoice', [BookingController::class, 'invoice'])->name('history.invoice');
    Route::get('/history/{booking}/medical-record', [BookingController::class, 'medicalRecord'])->name('history.medical-record');

    Route::middleware('role:patient')->group(function () {
        Route::post('/booking', [BookingController::class, 'store'])->name('booking.store');
        Route::get('/history', [BookingController::class, 'history'])->name('history.index');
        Route::post('/history/{booking}/check-payment', [BookingController::class, 'checkPayment'])->name('history.check-payment');
        Route::get('/payments/midtrans/finish', [BookingController::class, 'paymentFinish'])->name('payment.finish');
        Route::get('/payments/midtrans/unfinish', [BookingController::class, 'paymentUnfinish'])->name('payment.unfinish');
        Route::get('/payments/midtrans/error', [BookingController::class, 'paymentError'])->name('payment.error');
    });

    Route::prefix('doctor')->name('doctor.')->middleware('role:doctor')->group(function () {
        Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
        Route::get('/medical-notes', [MedicalNoteController::class, 'index'])->name('medical-notes.index');
        Route::get('/medical-notes/{booking}', [MedicalNoteController::class, 'create'])->name('medical-notes.create');
        Route::post('/medical-notes/{booking}', [MedicalNoteController::class, 'store'])->name('medical-notes.store');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function () {
        Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
        Route::get('/payments', [AdminPaymentController::class, 'index'])->name('payments.index');

        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::patch('/users/{user}/status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

        Route::get('/services', [AdminServiceController::class, 'index'])->name('services.index');
        Route::get('/services/create', [AdminServiceController::class, 'create'])->name('services.create');
        Route::post('/services', [AdminServiceController::class, 'store'])->name('services.store');
        Route::get('/services/{service}/edit', [AdminServiceController::class, 'edit'])->name('services.edit');
        Route::put('/services/{service}', [AdminServiceController::class, 'update'])->name('services.update');

        Route::get('/doctors', [AdminDoctorController::class, 'index'])->name('doctors.index');
        Route::get('/doctors/create', [AdminDoctorController::class, 'create'])->name('doctors.create');
        Route::post('/doctors', [AdminDoctorController::class, 'store'])->name('doctors.store');
        Route::get('/doctors/{doctor}/edit', [AdminDoctorController::class, 'edit'])->name('doctors.edit');
        Route::put('/doctors/{doctor}', [AdminDoctorController::class, 'update'])->name('doctors.update');

        Route::get('/schedules', [AdminScheduleController::class, 'index'])->name('schedules.index');
        Route::get('/schedules/create', [AdminScheduleController::class, 'create'])->name('schedules.create');
        Route::post('/schedules', [AdminScheduleController::class, 'store'])->name('schedules.store');
        Route::get('/schedules/{schedule}/edit', [AdminScheduleController::class, 'edit'])->name('schedules.edit');
        Route::put('/schedules/{schedule}', [AdminScheduleController::class, 'update'])->name('schedules.update');
    });
});

require __DIR__.'/auth.php';
