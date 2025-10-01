<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\InventoryPackageController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InventoryActionController;
use App\Http\Controllers\UtilityController;
use App\Http\Controllers\RoleController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth','role:Admin'])->group(function () {
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::post('/permissions/update', [PermissionController::class, 'update'])->name('permissions.update');
    Route::post('/permissions/store', [PermissionController::class, 'storePermission'])->name('permissions.store');
    Route::resource('roles', RoleController::class)->only(['index', 'create', 'store', 'destroy']);
});

// Route::get('/inventory', [InventoryController::class, 'index'])
//     ->middleware('permission:manage inventory');

Route::middleware(['auth','role:Admin'])->group(function () {
    Route::resource('clients', ClientController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('channels', ChannelController::class);
    Route::resource('inventories', InventoryController::class);

    Route::post('/inventories/{inventory}/ping',   [InventoryActionController::class, 'ping'])->name('inventories.ping');
    Route::post('/inventories/{inventory}/reboot', [InventoryActionController::class, 'reboot'])->name('inventories.reboot');

    Route::resource('packages', PackageController::class);

    //Package allocation to the inventory
    Route::get('/inventory-packages', [InventoryPackageController::class, 'index'])->name('inventory-packages.index');
    Route::post('/inventory-packages/{inventory}/assign', [InventoryPackageController::class, 'assign'])->name('inventory-packages.assign');

    Route::get('/utility/online', [UtilityController::class, 'index'])
        ->name('utility.online');

    //Download the reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/preview', [ReportController::class, 'preview'])->name('reports.preview');
    Route::get('/reports/download', [ReportController::class, 'download'])->name('reports.download');


    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
});


require __DIR__.'/auth.php';
