<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Order::class);

        $orders = Order::with('customer', 'items.product')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->customer_id, fn($q) => $q->where('customer_id', $request->customer_id))
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request): OrderResource|JsonResponse
    {
        Gate::authorize('create', Order::class);

        try {
            return DB::transaction(function () use ($request) {
                $validated = $request->validated();
                $tenantId = auth()->user()->tenant_id;

                // Verify customer belongs to tenant
                $customer = \App\Models\Customer::findOrFail($validated['customer_id']);
                if ($customer->tenant_id !== $tenantId) {
                    abort(403, 'Unauthorized');
                }

                $total = 0;
                $orderItems = [];

                foreach ($validated['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);

                    if ($product->tenant_id !== $tenantId) {
                        abort(403, 'Unauthorized');
                    }

                    if ($product->stock_quantity < $item['quantity']) {
                        return response()->json([
                            'message' => "Insufficient stock for product: {$product->name}",
                            'code' => 'INSUFFICIENT_STOCK',
                        ], 422);
                    }

                    $subtotal = $product->price * $item['quantity'];
                    $total += $subtotal;

                    $orderItems[] = [
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'unit_price' => $product->price,
                        'subtotal' => $subtotal,
                    ];

                    $product->decrement('stock_quantity', $item['quantity']);
                }

                $order = Order::create([
                    'tenant_id' => $tenantId,
                    'customer_id' => $validated['customer_id'],
                    'order_number' => 'ORD-' . strtoupper(Str::random(10)),
                    'total_amount' => $total,
                    'status' => 'pending',
                ]);

                foreach ($orderItems as $item) {
                    $order->items()->create($item);
                }

                return new OrderResource($order->load('customer', 'items.product'));
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show(Order $order): OrderResource
    {
        Gate::authorize('view', $order);
        return new OrderResource($order->load('customer', 'items.product'));
    }

    public function markAsPaid(Order $order): OrderResource|JsonResponse
    {
        Gate::authorize('update', $order);

        if (!$order->isPending()) {
            return response()->json([
                'message' => 'Only pending orders can be marked as paid',
                'code' => 'INVALID_STATUS',
            ], 422);
        }

        $order->update(['status' => 'paid']);
        return new OrderResource($order->load('customer', 'items.product'));
    }

    public function cancel(Order $order): OrderResource|JsonResponse
    {
        Gate::authorize('update', $order);

        if ($order->isCancelled()) {
            return response()->json([
                'message' => 'Order is already cancelled',
                'code' => 'ALREADY_CANCELLED',
            ], 422);
        }

        return DB::transaction(function () use ($order) {
            // Restore stock
            foreach ($order->items as $item) {
                $item->product->increment('stock_quantity', $item->quantity);
            }

            $order->update(['status' => 'cancelled']);
            return new OrderResource($order->load('customer', 'items.product'));
        });
    }

    public function destroy(Order $order): Response
    {
        Gate::authorize('delete', $order);
        $order->delete();
        return response()->noContent();
    }
}
