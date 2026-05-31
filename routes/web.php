<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\DoctorRequestController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SpecialtyController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\PageContentController;
use App\Http\Controllers\Doctor\AvailabilityController;
use App\Http\Controllers\Doctor\DashboardController as DoctorDashboardController;
use App\Http\Controllers\Doctor\ProfileController as DoctorProfileController;
use App\Http\Controllers\Doctor\ReportController as DoctorReportController;
use App\Http\Controllers\Doctor\AppointmentController as DoctorAppointmentController;
use App\Http\Controllers\Patient\BookingController;
use App\Http\Controllers\Patient\DashboardController as PatientDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicPageController;
use App\Models\Specialty;
use App\Models\User;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;


Route::get('/', function () {
    // Make landing page robust (fresh installs / test DBs might not have all tables).
    $specialties = collect();
    $doctors = collect();

    if (Schema::hasTable('specialties')) {
        $specialties = Specialty::where('is_active', true)->orderBy('name')->limit(8)->get();
    }

    if (Schema::hasTable('doctor_profiles')) {
        $doctors = User::where('role', 'doctor')
            ->whereHas('doctorProfile', fn ($query) => $query->where('status', 'approved'))
            ->with(['doctorProfile', 'doctorSpecialties'])
            ->limit(6)
            ->get();
    }

    return view('welcome', compact('specialties', 'doctors'));
});

Route::get('/doctors', [PublicPageController::class, 'doctors'])->name('public.doctors');
Route::get('/specialties', [PublicPageController::class, 'specialties'])->name('public.specialties');
Route::get('/about', [PublicPageController::class, 'about'])->name('public.about');
Route::get('/contact', [PublicPageController::class, 'contact'])->name('public.contact');

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }

    if ($user->role === 'doctor') {
        return redirect()->route('doctor.dashboard');
    }

    return redirect()->route('patient.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
});

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('specialties', SpecialtyController::class)->except(['create', 'show', 'edit']);
    Route::get('/doctor-requests', [DoctorRequestController::class, 'index'])->name('doctor-requests.index');
    Route::post('/doctor-requests/{doctor}/approve', [DoctorRequestController::class, 'approve'])->name('doctor-requests.approve');
    Route::post('/doctor-requests/{doctor}/reject', [DoctorRequestController::class, 'reject'])->name('doctor-requests.reject');
    Route::get('/users', [UserManagementController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserManagementController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [UserManagementController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('users.toggle-active');
    Route::post('/users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/{user}/notify', [UserManagementController::class, 'notify'])->name('users.notify');
    Route::delete('/users/{user}', [UserManagementController::class, 'destroy'])->name('users.destroy');
    Route::get('/reports', [AdminReportController::class, 'index'])->name('reports.index');
    Route::get('/appointments', [AdminAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/pages', [PageContentController::class, 'index'])->name('pages.index');
    Route::get('/pages/{page}/edit', [PageContentController::class, 'edit'])->name('pages.edit');
    Route::patch('/pages/{page}', [PageContentController::class, 'update'])->name('pages.update');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
});

Route::prefix('doctor')->name('doctor.')->middleware(['auth', 'verified', 'role:doctor'])->group(function () {
    Route::get('/dashboard', [DoctorDashboardController::class, 'index'])->name('dashboard');
    Route::get('/profile', [DoctorProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [DoctorProfileController::class, 'update'])->name('profile.update');
    Route::resource('availability', AvailabilityController::class)->except(['show', 'create', 'edit']);
    Route::get('/reports', [DoctorReportController::class, 'index'])->name('reports.index');
    Route::get('/appointments', [DoctorAppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/{appointment}/edit', [DoctorAppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/appointments/{appointment}', [DoctorAppointmentController::class, 'update'])->name('appointments.update');
    Route::post('/appointments/{appointment}/status', [DoctorAppointmentController::class, 'updateStatus'])->name('appointments.status');
});

// Patient area (appointments list + booking)
Route::middleware(['auth', 'verified', 'role:patient'])->group(function () {
    Route::get('/patient/dashboard', [PatientDashboardController::class, 'index'])->name('patient.dashboard');
    Route::get('/bookings/create', [BookingController::class, 'create'])->name('bookings.create');
    Route::post('/bookings', [BookingController::class, 'store'])->name('bookings.store');
});

require __DIR__.'/auth.php';