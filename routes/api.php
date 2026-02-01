<?php

use Illuminate\Support\Facades\Route;
use App\Application\Http\Controllers\Api\V1\Auth\AuthController;
use App\Application\Http\Controllers\Api\V1\Organization\BranchController;
use App\Application\Http\Controllers\Api\V1\Menu\CategoryController;
use App\Application\Http\Controllers\Api\V1\Menu\ProductController;
use App\Application\Http\Controllers\Api\V1\Menu\ModifierController;
use App\Application\Http\Controllers\Api\V1\Floor\HallController;
use App\Application\Http\Controllers\Api\V1\Floor\TableController;
use App\Application\Http\Controllers\Api\V1\Reservation\ReservationController;
use App\Application\Http\Controllers\Api\V1\Order\OrderController;
use App\Application\Http\Controllers\Api\V1\Payment\CashShiftController;
use App\Application\Http\Controllers\Api\V1\Payment\PaymentController;
use App\Application\Http\Controllers\Api\V1\Kds\KdsController;
use App\Application\Http\Controllers\Api\V1\Customer\CustomerController;
use App\Application\Http\Controllers\Api\V1\Warehouse\StockController;
use App\Application\Http\Controllers\Api\V1\Warehouse\SupplyController;
use App\Application\Http\Controllers\Api\V1\Staff\EmployeeController;
use App\Application\Http\Controllers\Api\V1\Report\ReportController;
use App\Application\Http\Controllers\Api\V1\Delivery\DeliveryZoneController;
use App\Application\Http\Controllers\Api\V1\Delivery\CourierController;
use App\Application\Http\Controllers\Api\V1\Delivery\DeliveryOrderController;
use App\Application\Http\Controllers\Api\V1\Infrastructure\PrinterController;
use App\Application\Http\Controllers\Api\V1\Infrastructure\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API v1 Routes
Route::prefix('v1')->group(function () {

    // Public routes (no authentication required)
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('pin-login', [AuthController::class, 'pinLogin']);
        Route::post('register', [AuthController::class, 'register']);
    });

    // Protected routes (require authentication)
    Route::middleware(['auth:sanctum', 'organization'])->group(function () {

        // Auth routes
        Route::prefix('auth')->group(function () {
            Route::get('me', [AuthController::class, 'me']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('logout-all', [AuthController::class, 'logoutAll']);
        });

        // Branch management
        Route::apiResource('branches', BranchController::class);

        // Routes with branch access check
        Route::middleware('branch.access')->group(function () {

            // Menu routes
            Route::prefix('menu')->group(function () {
                Route::get('categories/tree', [CategoryController::class, 'tree']);
                Route::post('categories/reorder', [CategoryController::class, 'reorder']);
                Route::apiResource('categories', CategoryController::class);
                Route::post('products/bulk-status', [ProductController::class, 'bulkUpdateStatus']);
                Route::patch('products/{product}/price', [ProductController::class, 'updatePrice']);
                Route::apiResource('products', ProductController::class);
                Route::post('modifier-groups/{modifierGroup}/modifiers', [ModifierController::class, 'addModifier']);
                Route::apiResource('modifier-groups', ModifierController::class);
                Route::patch('modifiers/{modifier}', [ModifierController::class, 'updateModifier']);
                Route::delete('modifiers/{modifier}', [ModifierController::class, 'destroyModifier']);
            });

            // Floor management
            Route::prefix('floor')->group(function () {
                Route::apiResource('halls', HallController::class);
                Route::patch('tables/{table}/status', [TableController::class, 'updateStatus']);
                Route::patch('tables/{table}/position', [TableController::class, 'updatePosition']);
                Route::post('tables/bulk-positions', [TableController::class, 'bulkUpdatePositions']);
                Route::apiResource('tables', TableController::class);
            });

            // Reservations
            Route::get('reservations/available-tables', [ReservationController::class, 'availableTables']);
            Route::patch('reservations/{reservation}/status', [ReservationController::class, 'updateStatus']);
            Route::apiResource('reservations', ReservationController::class);

            // Orders
            Route::prefix('orders')->group(function () {
                Route::post('{order}/items', [OrderController::class, 'addItem']);
                Route::patch('{order}/items/{item}', [OrderController::class, 'updateItem']);
                Route::delete('{order}/items/{item}', [OrderController::class, 'removeItem']);
                Route::post('{order}/send-to-kitchen', [OrderController::class, 'sendToKitchen']);
                Route::post('{order}/discount', [OrderController::class, 'applyDiscount']);
                Route::post('{order}/transfer', [OrderController::class, 'transfer']);
                Route::post('{order}/close', [OrderController::class, 'close']);
                Route::post('{order}/cancel', [OrderController::class, 'cancel']);
            });
            Route::apiResource('orders', OrderController::class)->except(['update', 'destroy']);

            // Payments & Cash Shifts
            Route::prefix('cash-shifts')->group(function () {
                Route::get('current', [CashShiftController::class, 'current']);
                Route::post('open', [CashShiftController::class, 'open']);
                Route::post('{cashShift}/close', [CashShiftController::class, 'close']);
                Route::post('{cashShift}/cash-operation', [CashShiftController::class, 'addCashOperation']);
                Route::get('{cashShift}/report', [CashShiftController::class, 'report']);
            });
            Route::apiResource('cash-shifts', CashShiftController::class)->only(['index', 'show']);

            Route::prefix('payments')->group(function () {
                Route::get('methods', [PaymentController::class, 'methods']);
                Route::post('process', [PaymentController::class, 'process']);
                Route::post('{payment}/refund', [PaymentController::class, 'refund']);
            });

            // KDS (Kitchen Display System)
            Route::prefix('kds')->group(function () {
                Route::get('orders', [KdsController::class, 'orders']);
                Route::post('items/{item}/start', [KdsController::class, 'startPreparing']);
                Route::post('items/{item}/ready', [KdsController::class, 'markReady']);
                Route::post('items/{item}/served', [KdsController::class, 'markServed']);
                Route::get('statistics', [KdsController::class, 'statistics']);
            });

            // Customers & Loyalty
            Route::prefix('customers')->group(function () {
                Route::get('search', [CustomerController::class, 'search']);
                Route::get('{customer}/history', [CustomerController::class, 'history']);
                Route::post('{customer}/bonus', [CustomerController::class, 'addBonus']);
            });
            Route::apiResource('customers', CustomerController::class);

            // Warehouse
            Route::prefix('warehouse')->group(function () {
                Route::get('stock', [StockController::class, 'index']);
                Route::get('stock/low', [StockController::class, 'lowStock']);
                Route::post('stock/adjust', [StockController::class, 'adjust']);
                Route::apiResource('supplies', SupplyController::class);
                Route::post('supplies/{supply}/receive', [SupplyController::class, 'receive']);
            });

            // Staff
            Route::prefix('staff')->group(function () {
                Route::post('clock-in', [EmployeeController::class, 'clockIn']);
                Route::post('clock-out', [EmployeeController::class, 'clockOut']);
                Route::get('shifts', [EmployeeController::class, 'shifts']);
            });
            Route::apiResource('employees', EmployeeController::class);

            // Reports
            Route::prefix('reports')->group(function () {
                Route::get('dashboard', [ReportController::class, 'dashboard']);
                Route::get('sales', [ReportController::class, 'sales']);
                Route::get('products', [ReportController::class, 'products']);
                Route::get('employees', [ReportController::class, 'employees']);
                Route::get('export/{type}', [ReportController::class, 'export']);
            });

            // Delivery
            Route::prefix('delivery')->group(function () {
                Route::post('zones/check-point', [DeliveryZoneController::class, 'checkPoint']);
                Route::apiResource('zones', DeliveryZoneController::class);
                Route::prefix('couriers')->group(function () {
                    Route::patch('{courier}/location', [CourierController::class, 'updateLocation']);
                    Route::patch('{courier}/status', [CourierController::class, 'setStatus']);
                    Route::get('{courier}/deliveries', [CourierController::class, 'activeDeliveries']);
                });
                Route::apiResource('couriers', CourierController::class);
                Route::prefix('orders')->group(function () {
                    Route::post('{deliveryOrder}/assign', [DeliveryOrderController::class, 'assignCourier']);
                    Route::post('{deliveryOrder}/pickup', [DeliveryOrderController::class, 'pickUp']);
                    Route::post('{deliveryOrder}/deliver', [DeliveryOrderController::class, 'deliver']);
                    Route::post('{deliveryOrder}/cancel', [DeliveryOrderController::class, 'cancel']);
                    Route::post('{deliveryOrder}/rate', [DeliveryOrderController::class, 'rate']);
                });
                Route::apiResource('orders', DeliveryOrderController::class)->except(['destroy']);
            });

            // Printers
            Route::prefix('printers')->group(function () {
                Route::post('{printer}/test', [PrinterController::class, 'testConnection']);
            });
            Route::apiResource('printers', PrinterController::class);

            // Notifications
            Route::prefix('notifications')->group(function () {
                Route::get('unread-count', [NotificationController::class, 'unreadCount']);
                Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead']);
                Route::post('{notification}/read', [NotificationController::class, 'markAsRead']);
                Route::delete('all', [NotificationController::class, 'destroyAll']);
            });
            Route::apiResource('notifications', NotificationController::class)->only(['index', 'destroy']);
        });
    });
});
