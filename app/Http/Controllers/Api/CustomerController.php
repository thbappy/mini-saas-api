<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class CustomerController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', Customer::class);

        $customers = Customer::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('email', 'like', "%{$request->search}%"))
            ->paginate($request->per_page ?? 15);

        return CustomerResource::collection($customers);
    }

    public function store(StoreCustomerRequest $request): CustomerResource
    {
        Gate::authorize('create', Customer::class);
        $validatedData = $request->validated();
        $validatedData['tenant_id'] = auth()->user()->tenant_id;
        $customer = Customer::create($validatedData);
        return new CustomerResource($customer);
    }

    public function show(Customer $customer): CustomerResource
    {
        Gate::authorize('view', $customer);
        return new CustomerResource($customer);
    }

    public function update(Request $request, Customer $customer): CustomerResource
    {
        Gate::authorize('update', $customer);
        $customer->update($request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email',
            'phone' => 'sometimes|string|max:20',
            'address' => 'sometimes|string',
        ]));
        return new CustomerResource($customer);
    }

    public function destroy(Customer $customer): Response
    {
        Gate::authorize('delete', $customer);
        $customer->delete();
        return response()->noContent();
    }
}
