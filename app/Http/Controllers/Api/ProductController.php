<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Product::class);

        $products = Product::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->low_stock, fn($q) => $q->whereRaw('stock_quantity <= low_stock_threshold'))
            ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
            ->paginate($request->per_page ?? 15);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): ProductResource
    {
        Gate::authorize('create', Product::class);
        $validatedData = $request->validated();
        $validatedData['tenant_id'] = auth()->user()->tenant_id;
        $product = Product::create($validatedData);
        return new ProductResource($product);
    }

    public function show(Product $product): ProductResource
    {
        Gate::authorize('view', $product);
        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        Gate::authorize('update', $product);
        $product->update($request->validated());
        return new ProductResource($product);
    }

    public function destroy(Product $product): Response
    {
        Gate::authorize('delete', $product);
        $product->delete();
        return response()->noContent();
    }
}
