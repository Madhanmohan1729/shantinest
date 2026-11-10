<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PropertyController as AdminPropertyController;
use App\Http\Controllers\Admin\PropertyImageController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\RoomHoldController;
use App\Http\Controllers\Admin\StayRequestController as AdminStayRequestController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\StayRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PropertyController::class, 'index'])->name('home');
Route::get('stays/{property}', [PropertyController::class, 'show'])->name('properties.show');
Route::post('stays/{property}/requests', [StayRequestController::class, 'store'])
    ->middleware('throttle:6,1')
    ->name('stay-requests.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->middleware('can:view dashboard')->name('dashboard');
});

Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('properties', [AdminPropertyController::class, 'index'])->middleware('can:view properties')->name('properties.index');
    Route::get('properties/create', [AdminPropertyController::class, 'create'])->middleware('can:create properties')->name('properties.create');
    Route::post('properties', [AdminPropertyController::class, 'store'])->middleware('can:create properties')->name('properties.store');
    Route::get('properties/{property}/edit', [AdminPropertyController::class, 'edit'])->middleware('can:view properties')->name('properties.edit');
    Route::put('properties/{property}', [AdminPropertyController::class, 'update'])->middleware('can:update properties')->name('properties.update');
    Route::delete('properties/{property}', [AdminPropertyController::class, 'destroy'])->middleware('can:delete properties')->name('properties.destroy');

    Route::middleware('can:manage property images')->group(function () {
        Route::post('properties/{property}/images', [PropertyImageController::class, 'store'])->name('property-images.store');
        Route::put('properties/{property}/images/order', [PropertyImageController::class, 'reorder'])->name('property-images.reorder');
        Route::patch('property-images/{image}', [PropertyImageController::class, 'update'])->name('property-images.update');
        Route::delete('property-images/{image}', [PropertyImageController::class, 'destroy'])->name('property-images.destroy');
    });

    Route::middleware('can:manage rooms')->group(function () {
        Route::post('properties/{property}/rooms', [RoomController::class, 'store'])->name('rooms.store');
        Route::put('rooms/{room}', [RoomController::class, 'update'])->name('rooms.update');
        Route::delete('rooms/{room}', [RoomController::class, 'destroy'])->name('rooms.destroy');
    });

    Route::get('holds', [RoomHoldController::class, 'index'])->middleware('can:view holds')->name('holds.index');
    Route::middleware('can:manage holds')->group(function () {
        Route::post('holds', [RoomHoldController::class, 'store'])->name('holds.store');
        Route::put('holds/{hold}', [RoomHoldController::class, 'update'])->name('holds.update');
        Route::delete('holds/{hold}', [RoomHoldController::class, 'destroy'])->name('holds.destroy');
    });

    Route::get('stay-requests', [AdminStayRequestController::class, 'index'])->middleware('can:view stay requests')->name('stay-requests.index');
    Route::middleware('can:manage stay requests')->group(function () {
        Route::post('stay-requests/{stayRequest}/approve', [AdminStayRequestController::class, 'approve'])->name('stay-requests.approve');
        Route::post('stay-requests/{stayRequest}/decline', [AdminStayRequestController::class, 'decline'])->name('stay-requests.decline');
    });
});

require __DIR__.'/settings.php';
