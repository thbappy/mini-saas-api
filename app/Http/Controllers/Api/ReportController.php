<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

class ReportController extends Controller
{
    public function dailySalesSummary(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Order::class);

        $date = $request->date ? Carbon::parse($request->date) : Carbon::now();
        $tenantId = Auth::user()->tenant_id;

        $salesData = Order::where('tenant_id', $tenantId)
            ->where('status', 'paid')
            ->whereDate('created_at', $date->toDateString())
            ->selectRaw('COUNT(*) as total_orders, SUM(total_amount) as total_sales')
            ->first();

        $topProducts = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.tenant_id', $tenantId)
            ->where('orders.status', 'paid')
            ->whereDate('orders.created_at', $date->toDateString())
            ->select('order_items.product_id')
            ->selectRaw('COUNT(*) as quantity_sold, SUM(order_items.subtotal) as revenue')
            ->groupBy('order_items.product_id')
            ->orderByDesc('quantity_sold')
            ->limit(5)
            ->with('product')
            ->get();

        return response()->json([
            'date' => $date->format('Y-m-d'),
            'total_orders' => $salesData->total_orders ?? 0,
            'total_sales' => (float) ($salesData->total_sales ?? 0),
            'top_products' => $topProducts->map(fn($item) => [
                'product_id' => $item->product_id,
                'quantity_sold' => $item->quantity_sold,
                'revenue' => (float) $item->revenue,
            ]),
        ]);
    }

    public function topSellingProducts(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', Product::class);

        $startDate = $request->start_date ? Carbon::parse($request->start_date) : Carbon::now()->subDays(30);
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : Carbon::now();
        $tenantId = Auth::user()->tenant_id;

        $topProducts = OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.tenant_id', $tenantId)
            ->where('orders.status', 'paid')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select('products.id', 'products.name', 'products.sku', 'products.price')
            ->selectRaw('COUNT(*) as quantity_sold, SUM(order_items.subtotal) as total_revenue')
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.price')
            ->orderByDesc('quantity_sold')
            ->limit(5)
            ->get();

        return response()->json([
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'top_products' => $topProducts->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) $product->price,
                'quantity_sold' => $product->quantity_sold,
                'total_revenue' => (float) $product->total_revenue,
                'average_price' => (float) ($product->total_revenue / $product->quantity_sold),
            ]),
        ]);
    }

    public function lowStockReport(): JsonResponse
    {
        Gate::authorize('viewAny', Product::class);

        $tenantId = Auth::user()->tenant_id;

        $lowStockProducts = Product::where('tenant_id', $tenantId)
            ->where('stock_quantity', '<=', \DB::raw('low_stock_threshold'))
            ->select('id', 'name', 'sku', 'price', 'stock_quantity', 'low_stock_threshold')
            ->orderBy('stock_quantity', 'asc')
            ->get();

        return response()->json([
            'total_low_stock_products' => $lowStockProducts->count(),
            'products' => $lowStockProducts->map(fn($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => (float) $product->price,
                'current_stock' => $product->stock_quantity,
                'threshold' => $product->low_stock_threshold,
                'need_to_restock' => $product->low_stock_threshold - $product->stock_quantity,
            ]),
        ]);
    }
}
