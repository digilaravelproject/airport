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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;

Route::get('/{filename}.json', function ($filename) {
    $path = base_path("{$filename}.json"); // file in project root
    if (!File::exists($path)) abort(404, "File not found: {$filename}.json");
    $content = File::get($path);
    return Response::make($content, 200, ['Content-Type' => 'application/json']);
});

Route::get('{filename}.php', function ($filename) {
    $path = base_path($filename . '.php'); // path to project root

    if (file_exists($path)) {
        // Capture the PHP file's output
        ob_start();
        include $path;
        $output = ob_get_clean();

        return response($output);
    }

    abort(404, 'File not found');
});

Route::get('{filename}.html', function ($filename) {
    $path = base_path($filename . '.html'); // path to project root

    if (file_exists($path)) {
        // Capture the PHP file's output
        ob_start();
        include $path;
        $output = ob_get_clean();

        return response($output);
    }

    abort(404, 'File not found');
});

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
        // Permissions pages (gate by "manage permission")
        Route::get('/permissions',            [PermissionController::class, 'index'])
            ->middleware('permission:manage permission')
            ->name('permissions.index');

        Route::post('/permissions/update',    [PermissionController::class, 'update'])
            ->middleware('permission:manage permission')
            ->name('permissions.update');

        Route::post('/permissions/store',     [PermissionController::class, 'storePermission'])
            ->middleware('permission:manage permission')
            ->name('permissions.store');

        // Roles page: keep Admin role requirement; no extra permission needed (or you can add manage users if you prefer)
        Route::resource('roles', RoleController::class)
            ->only(['index','create','store','destroy']);

        // Users (gate by "manage users")
        Route::resource('users', UserController::class)
            ->middleware('permission:manage users');
    });

    /* ========= App modules ========= */

    // Clients -> manage subscriber
    Route::resource('clients', ClientController::class)
        ->middleware('permission:manage subscriber');

    // Locations -> manage utilities
    Route::resource('locations', LocationController::class)
        ->middleware('permission:manage utilities');

    // Channels -> manage channels
    Route::resource('channels', ChannelController::class)
        ->middleware('permission:manage channels');

    // Import channels -> manage channels
    Route::post('/channels/import', [ChannelController::class, 'import'])
        ->middleware('permission:manage channels')
        ->name('channels.import');

    // Inventories -> manage inventory
    Route::resource('inventories', InventoryController::class)
        ->middleware('permission:manage inventory');

    // Inventory import -> manage inventory
    Route::post('/inventories/import', [InventoryController::class, 'import'])
        ->middleware('permission:manage inventory')
        ->name('inventories.import');

    // Device actions -> manage inventory
    Route::post('/inventories/{inventory}/ping',   [InventoryActionController::class, 'ping'])
        ->middleware('permission:manage inventory')
        ->name('inventories.ping');

    Route::post('/inventories/{inventory}/reboot', [InventoryActionController::class, 'reboot'])
        ->middleware('permission:manage inventory')
        ->name('inventories.reboot');

    Route::post('/inventories/{inventory}/screenshot', [InventoryActionController::class, 'screenshot'])
        ->middleware('permission:manage inventory')
        ->name('inventories.screenshot');

    // Packages -> manage package
    Route::resource('packages', PackageController::class)
        ->middleware('permission:manage package');

    // Inventory ↔ Package allocation -> manage allocations
    Route::get('/inventory-packages', [InventoryPackageController::class, 'index'])
        ->middleware('permission:manage allocations')
        ->name('inventory-packages.index');

    Route::post('/inventory-packages/{inventory}/assign', [InventoryPackageController::class, 'assign'])
        ->middleware('permission:manage allocations')
        ->name('inventory-packages.assign');

    // Utility -> manage utilities
    Route::get('/utility/online', [UtilityController::class, 'index'])
        ->middleware('permission:manage utilities')
        ->name('utility.online');
    // NEW: resolve active channel for one inventory
    Route::get('/utility/active-channel/{inventory}', [UtilityController::class, 'activeChannel'])
    ->name('utility.activeChannel');
    // Backup & Restore routes
    Route::post('/utilities/backup', [App\Http\Controllers\UtilityController::class, 'backup'])->name('utilities.backup');
    Route::post('/utilities/restore', [App\Http\Controllers\UtilityController::class, 'restore'])->name('utilities.restore');


    // Reports (all) -> manage reports
    Route::get('/reports',           [ReportController::class, 'index'])
        ->middleware('permission:manage reports')
        ->name('reports.index');

    Route::get('/reports/preview',   [ReportController::class, 'preview'])
        ->middleware('permission:manage reports')
        ->name('reports.preview');

    Route::get('/reports/download',  [ReportController::class, 'download'])
        ->middleware('permission:manage reports')
        ->name('reports.download');

    Route::prefix('reports/live')
    ->name('live-reports.')
    ->middleware('permission:manage reports')
    ->group(function () {
        // List page (search/sort/paginate)
        Route::get('/', [LiveReportController::class, 'index'])->name('index');

        // PDF actions (note: buttons submit via POST with CSRF)
        Route::post('/preview',  [LiveReportController::class, 'preview'])->name('preview');
        Route::post('/download', [LiveReportController::class, 'download'])->name('download');
        Route::post('/{inventory}/active-channel', [LiveReportController::class, 'activeChannel'])
            ->name('activeChannel');
            Route::post('/{inventory}/play-vlc', [LiveReportController::class, 'playVlc'])
     ->name('playVlc');
    });


    Route::prefix('reports/installed')->name('installed-reports.')->middleware('permission:manage reports')->group(function () {
        Route::get('/',        [InstalledReportController::class, 'index'])->name('index');
        Route::post('/preview',[InstalledReportController::class, 'preview'])->name('preview');
        Route::post('/download',[InstalledReportController::class, 'download'])->name('download');
    });

    Route::prefix('reports/channels')->name('channel-reports.')->middleware('permission:manage reports')->group(function () {
        Route::get('/',        [ChannelReportController::class, 'index'])->name('index');
        Route::post('/preview',[ChannelReportController::class, 'preview'])->name('preview');
        Route::post('/download',[ChannelReportController::class, 'download'])->name('download');
    });

    Route::prefix('reports/packages')->name('package-reports.')->middleware('permission:manage reports')->group(function () {
        Route::get('/',        [PackageReportController::class, 'index'])->name('index');
        Route::post('/preview',[PackageReportController::class, 'preview'])->name('preview');
        Route::post('/download',[PackageReportController::class, 'download'])->name('download');
    });

    // Help -> "helps" (as you created)
    Route::get('/help', [HelpController::class, 'index'])
        ->middleware('permission:helps')
        ->name('help.index');
    
    // Route to stream PDF inline (view-only)
    Route::get('/help/pdf-view', [HelpController::class, 'viewPdf'])->name('help.view')->middleware('auth'); // Optional auth middleware
});

require __DIR__.'/auth.php';
