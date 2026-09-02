<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Checkout\CreateCheckout;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Checkout\CheckoutRequest;
use App\Http\Resources\Api\V1\OrderResource;
use App\Support\Api\RespondsWithApiEnvelope;
use Illuminate\Http\JsonResponse;

class CheckoutController extends Controller
{
    use RespondsWithApiEnvelope;

    public function store(CheckoutRequest $request, CreateCheckout $checkout): JsonResponse
    {
        $result = $checkout->execute($request->user(), $request->validated());

        return $this->success([
            'order' => (new OrderResource($result['order']))->resolve($request),
            'checkout_url' => $result['checkout_url'],
        ], __('Checkout created successfully.'), 201);
    }
}
