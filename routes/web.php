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
use App\Http\Controllers\UserController;
use App\Http\Controllers\LiveReportController;
use App\Http\Controllers\InstalledReportController;
use App\Http\Controllers\ChannelReportController;
use App\Http\Controllers\PackageReportController;
use Illuminate\Support\Facades\Route;

// Route::get('/', fn () => view('welcome'));
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', fn () => view('dashboard'))
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    // Profile
    Route::get('/profile',  [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',[ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',[ProfileController::class, 'destroy'])->name('profile.destroy');

    /* ========= Admin-only: Role/Permission management ========= */
    Route::middleware('role:Admin')->group(function () {
        Route::get('/permissions',            [PermissionController::class, 'index'])
            ->middleware('permission:permissions.index')
            ->name('permissions.index');

        Route::post('/permissions/update',    [PermissionController::class, 'update'])
            ->middleware('permission:permissions.update')
            ->name('permissions.update');

        Route::post('/permissions/store',     [PermissionController::class, 'storePermission'])
            ->middleware('permission:permissions.store')
            ->name('permissions.store');

        Route::resource('roles', RoleController::class)
            ->only(['index','create','store','destroy'])
            ->middleware('permission:roles.index|roles.create|roles.store|roles.destroy');

          Route::resource('users', UserController::class);
    });

    /* ========= App modules: permission-gated (no role:Admin here) ========= */

    // Clients
    Route::resource('clients', ClientController::class)
        ->middleware('permission:clients.index|clients.show|clients.create|clients.store|clients.edit|clients.update|clients.destroy');

    // Locations
    Route::resource('locations', LocationController::class)
        ->middleware('permission:locations.index|locations.show|locations.create|locations.store|locations.edit|locations.update|locations.destroy');

    // Channels
    Route::resource('channels', ChannelController::class)
        ->middleware('permission:channels.index|channels.show|channels.create|channels.store|channels.edit|channels.update|channels.destroy');
    // routes/web.php
    Route::post('/channels/import', [\App\Http\Controllers\ChannelController::class, 'import'])
    ->name('channels.import');


    // Inventories
    Route::resource('inventories', InventoryController::class)
        ->middleware('permission:inventories.index|inventories.show|inventories.create|inventories.store|inventories.edit|inventories.update|inventories.destroy');

    Route::post('/inventories/import', [InventoryController::class, 'import'])->name('inventories.import');

    // Device actions
    Route::post('/inventories/{inventory}/ping',   [InventoryActionController::class, 'ping'])
        ->middleware('permission:inventories.ping')
        ->name('inventories.ping');

    Route::post('/inventories/{inventory}/reboot', [InventoryActionController::class, 'reboot'])
        ->middleware('permission:inventories.reboot')
        ->name('inventories.reboot');

    Route::post('/inventories/{inventory}/screenshot', [InventoryActionController::class, 'screenshot'])
    ->name('inventories.screenshot');

    // Packages
    Route::resource('packages', PackageController::class)
        ->middleware('permission:packages.index|packages.show|packages.create|packages.store|packages.edit|packages.update|packages.destroy');

    // Inventory ↔ Package allocation
    Route::get('/inventory-packages', [InventoryPackageController::class, 'index'])
        ->middleware('permission:inventory-packages.index')
        ->name('inventory-packages.index');

    Route::post('/inventory-packages/{inventory}/assign', [InventoryPackageController::class, 'assign'])
        ->middleware('permission:inventory-packages.assign')
        ->name('inventory-packages.assign');

    // Utility
    Route::get('/utility/online', [UtilityController::class, 'index'])
        ->middleware('permission:utility.online')
        ->name('utility.online');

    // Reports
    Route::get('/reports',           [ReportController::class, 'index'])  ->middleware('permission:reports.index')->name('reports.index');
    Route::get('/reports/preview',   [ReportController::class, 'preview'])->middleware('permission:reports.preview')->name('reports.preview');
    Route::get('/reports/download',  [ReportController::class, 'download'])->middleware('permission:reports.download')->name('reports.download');

    Route::prefix('reports/live')->name('live-reports.')->group(function () {
        Route::get('/', [LiveReportController::class, 'index'])
            ->middleware('permission:live-reports.index')
            ->name('index');

        Route::get('/preview', [LiveReportController::class, 'preview'])
            ->middleware('permission:live-reports.preview')
            ->name('preview');

        Route::get('/download', [LiveReportController::class, 'download'])
            ->middleware('permission:live-reports.download')
            ->name('download');
    });

    Route::prefix('reports/installed')->name('installed-reports.')->group(function () {
        Route::get('/', [InstalledReportController::class, 'index'])
            ->middleware('permission:installed-reports.index')
            ->name('index');
        Route::post('/preview', [InstalledReportController::class, 'preview'])
            ->middleware('permission:installed-reports.preview')
            ->name('preview');
        Route::post('/download', [InstalledReportController::class, 'download'])
            ->middleware('permission:installed-reports.download')
            ->name('download');
    });

    // Channels
    Route::prefix('reports/channels')->name('channel-reports.')->group(function () {
        Route::get('/', [ChannelReportController::class, 'index'])
            ->middleware('permission:channel-reports.index')
            ->name('index');

        Route::post('/preview', [ChannelReportController::class, 'preview'])
            ->middleware('permission:channel-reports.preview')
            ->name('preview');

        Route::post('/download', [ChannelReportController::class, 'download'])
            ->middleware('permission:channel-reports.download')
            ->name('download');
    });

    // Packages
    Route::prefix('reports/packages')->name('package-reports.')->group(function () {
        Route::get('/', [PackageReportController::class, 'index'])
            ->middleware('permission:package-reports.index')
            ->name('index');

        Route::post('/preview', [PackageReportController::class, 'preview'])
            ->middleware('permission:package-reports.preview')
            ->name('preview');

        Route::post('/download', [PackageReportController::class, 'download'])
            ->middleware('permission:package-reports.download')
            ->name('download');
    });

    // Help
    Route::get('/help', [HelpController::class, 'index'])
        ->middleware('permission:help.index')
        ->name('help.index');
});

require __DIR__.'/auth.php';
