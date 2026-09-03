<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Address\DeleteCustomerAddress;
use App\Actions\Address\SaveCustomerAddress;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Address\StoreCustomerAddressRequest;
use App\Http\Requests\Api\V1\Address\UpdateCustomerAddressRequest;
use App\Http\Resources\Api\V1\CustomerAddressResource;
use App\Models\CustomerAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class CustomerAddressController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        Gate::authorize('viewAny', CustomerAddress::class);

        return CustomerAddressResource::collection(
            request()->user()->addresses()->orderByDesc('is_default')->latest()->get()
        );
    }

    public function store(StoreCustomerAddressRequest $request, SaveCustomerAddress $action): CustomerAddressResource
    {
        return new CustomerAddressResource($action->handle($request->user(), $request->validated()));
    }

    public function show(CustomerAddress $address): CustomerAddressResource
    {
        Gate::authorize('view', $address);

        return new CustomerAddressResource($address);
    }

    public function update(UpdateCustomerAddressRequest $request, CustomerAddress $address, SaveCustomerAddress $action): CustomerAddressResource
    {
        return new CustomerAddressResource($action->handle($request->user(), $request->validated(), $address));
    }

    public function destroy(CustomerAddress $address, DeleteCustomerAddress $action): JsonResponse
    {
        Gate::authorize('delete', $address);
        $action->handle($address);

        return response()->json([
            'data' => null,
            'message' => __('Address deleted successfully.'),
            'errors' => null,
        ]);
    }
}
