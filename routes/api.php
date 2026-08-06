<?php

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\ServiceController as AdminServiceController;
use App\Http\Controllers\Api\ServiceController as ApiServiceController;
use App\Http\Controllers\Admin\AuthController as AuthController;
use App\Http\Controllers\Admin\AdminSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'innovation-tech Engineering API is running',
        'timestamp' => now()
    ]);
});

Route::get('/', function () {
    return response()->json(['message' => 'Welcome to innovation-tech Engineering API']);
});

// Admin Authentication Routes
Route::group(['prefix' => 'admin/auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected auth routes
    Route::middleware('auth:api')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/profile', [AuthController::class, 'profile']);

        Route::group(['prefix' => 'settings'], function () {
            Route::post('/request-update', [AdminSettingsController::class, 'requestUpdate']);
            Route::post('/confirm-update', [AdminSettingsController::class, 'confirmUpdate']);
        });

    });

});

// Admin Dashboard Routes
Route::group(['prefix' => 'admin', 'middleware' => ['auth:api']], function () {
    
    // User Management
    Route::group(['prefix' => 'users'], function () {
        Route::get('/', [AdminController::class, 'getUsers']);
        Route::get('/{encodedId}', [AdminController::class, 'getUser']);
        Route::post('/', [AdminController::class, 'createUser']);
        Route::put('/{encodedId}', [AdminController::class, 'updateUser']);
        Route::delete('/{encodedId}', [AdminController::class, 'deleteUser']);
        Route::patch('/{encodedId}/toggle-active', [AdminController::class, 'toggleActive']);
        
        // Bulk operations
        Route::post('/bulk/delete', [AdminController::class, 'bulkDelete']);
        Route::post('/bulk/update-status', [AdminController::class, 'bulkUpdateStatus']);
    });
    
    // Permission Management
    Route::group(['prefix' => 'permissions'], function () {
        Route::get('/', [AdminController::class, 'getPermissions']);
        Route::post('/assign/{encodedId}', [AdminController::class, 'assignPermissions']);
    });

    // Services Management
    Route::group(['prefix' => 'services'], function () {
        Route::get('/', [AdminServiceController::class, 'index']);
        Route::get('/{encodedId}', [AdminServiceController::class, 'show']);
        Route::get('/slug/{slug}', [AdminServiceController::class, 'getBySlug']);
        Route::post('/', [AdminServiceController::class, 'store']);
        Route::post('/{encodedId}', [AdminServiceController::class, 'update']);
        Route::delete('/{encodedId}', [AdminServiceController::class, 'destroy']);
        Route::patch('/{encodedId}/toggle-active', [AdminServiceController::class, 'toggleActive']);
        
        // Bulk operations
        Route::post('/bulk/delete', [AdminServiceController::class, 'bulkDelete']);
        Route::post('/bulk/update-status', [AdminServiceController::class, 'bulkUpdateStatus']);
    });

    Route::prefix('contacts')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\ContactController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Admin\ContactController::class, 'store']);
        Route::put('/{encodedId}', [App\Http\Controllers\Admin\ContactController::class, 'update']);
        Route::patch('/{encodedId}/toggle-active', [App\Http\Controllers\Admin\ContactController::class, 'toggleActive']);
        Route::delete('/{encodedId}', [App\Http\Controllers\Admin\ContactController::class, 'destroy']);
        
        // Bulk operations
        Route::post('/bulk/delete', [App\Http\Controllers\Admin\ContactController::class, 'bulkDelete']);
        Route::post('/bulk/update-status', [App\Http\Controllers\Admin\ContactController::class, 'bulkUpdateStatus']);
    });

    // settings
    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', [App\Http\Controllers\Admin\SettingsController::class, 'index']);
        Route::put('/', [App\Http\Controllers\Admin\SettingsController::class, 'update']);
    });

    // Site theme colors + logo
    Route::group(['prefix' => 'theme'], function () {
        Route::get('/', [App\Http\Controllers\Admin\ThemeController::class, 'show']);
        Route::put('/', [App\Http\Controllers\Admin\ThemeController::class, 'update']);
        Route::post('/reset', [App\Http\Controllers\Admin\ThemeController::class, 'reset']);
        Route::post('/logo', [App\Http\Controllers\Admin\ThemeController::class, 'uploadLogo']);
        Route::delete('/logo', [App\Http\Controllers\Admin\ThemeController::class, 'deleteLogo']);
    });

    // Team Members Management
    Route::prefix('team-members')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\TeamMemberController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Admin\TeamMemberController::class, 'store']);
        Route::post('/{encodedId}', [App\Http\Controllers\Admin\TeamMemberController::class, 'update']);
        Route::delete('/{encodedId}', [App\Http\Controllers\Admin\TeamMemberController::class, 'destroy']);
        Route::patch('/{encodedId}/toggle-active', [App\Http\Controllers\Admin\TeamMemberController::class, 'toggleActive']);
        Route::post('/bulk/delete', [App\Http\Controllers\Admin\TeamMemberController::class, 'bulkDelete']);
    });

    // Partners Management
    Route::prefix('partners')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\PartnerController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Admin\PartnerController::class, 'store']);
        Route::post('/{encodedId}', [App\Http\Controllers\Admin\PartnerController::class, 'update']);
        Route::delete('/{encodedId}', [App\Http\Controllers\Admin\PartnerController::class, 'destroy']);
        Route::patch('/{encodedId}/toggle-active', [App\Http\Controllers\Admin\PartnerController::class, 'toggleActive']);
    });

    // Page content (CMS sections per page)
    Route::prefix('pages')->group(function () {
        Route::get('/{pageKey}', [App\Http\Controllers\Admin\PageContentController::class, 'show']);
        Route::put('/{pageKey}', [App\Http\Controllers\Admin\PageContentController::class, 'update']);
    });

});

// Public API Routes (No Authentication Required)
Route::group(['prefix' => 'public'], function () {
    Route::group(['prefix' => 'services'], function () {
        Route::get('/', [ApiServiceController::class, 'index']);
        Route::get('/{slug}', [ApiServiceController::class, 'getBySlug']);
    });

    // Home Page
    Route::group(['prefix' => 'home'], function () {
        Route::get('/', [App\Http\Controllers\Api\HomeController::class, 'index']);
    });

    // About Page
    Route::group(['prefix' => 'about'], function () {
        Route::get('/', [App\Http\Controllers\Api\SettingsController::class, 'index']);
    });

    // Contact Page
    Route::group(['prefix' => 'contact'], function () {
        Route::get('/', [App\Http\Controllers\Api\ContactController::class, 'index']);
        Route::post('/', [App\Http\Controllers\Api\ContactController::class, 'store']);
    });

    // Site chrome (navbar + footer)
    Route::group(['prefix' => 'layout'], function () {
        Route::get('/', [App\Http\Controllers\Api\LayoutController::class, 'index']);
    });

});

// Legacy Sanctum route (keeping for compatibility)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
