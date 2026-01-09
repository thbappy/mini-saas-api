<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:60,1')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
});

Route::middleware('auth:sanctum', 'ensure.tenant', 'throttle:120,1')->group(function () {
    // Auth routes
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // Products
    Route::apiResource('products', ProductController::class);

    // Customers
    Route::apiResource('customers', CustomerController::class);

    // Orders
    Route::apiResource('orders', OrderController::class);
    Route::post('/orders/{order}/mark-as-paid', [OrderController::class, 'markAsPaid']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);

    // Reports
    Route::get('/reports/daily-sales', [ReportController::class, 'dailySalesSummary']);
    Route::get('/reports/top-selling-products', [ReportController::class, 'topSellingProducts']);
    Route::get('/reports/low-stock', [ReportController::class, 'lowStockReport']);
});
