<?php

use App\Http\Controllers\CheckInController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\MembershipPlanController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
  return Inertia::render('Welcome', [
    'canLogin' => Route::has('login'),
    'canRegister' => Route::has('register'),
    'laravelVersion' => Application::VERSION,
    'phpVersion' => PHP_VERSION,
  ]);
});

Route::get('/dashboard', function () {
  return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
  Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

  Route::resource('membership-plans', MembershipPlanController::class)
    ->except(['show']);
  Route::resource('members', MemberController::class);
  Route::get('/members/{member}/memberships/create', [MembershipController::class, 'create'])

    ->name('members.memberships.create');

  Route::post('/members/{member}/memberships', [MembershipController::class, 'store'])
    ->name('members.memberships.store');

  Route::post('/members/{member}/check-in', [CheckInController::class, 'storeFromMember'])
    ->name('members.check-in');

  Route::get('/check-in', [CheckInController::class, 'index'])->name('check-in.index');
  Route::post('/check-in', [CheckInController::class, 'store'])->name('check-in.store');
  Route::post('/check-in/{attendance}/cancel', [CheckInController::class, 'cancel'])->name('check-in.cancel');
});

require __DIR__ . '/auth.php';
