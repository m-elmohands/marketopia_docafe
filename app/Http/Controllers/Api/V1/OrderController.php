<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\OrderResource;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Gate;

class OrderController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return OrderResource::collection(Order::query()
            ->where('user_id', $request->user()->id)
            ->with('items')
            ->when(request('status'), fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(min(max((int) request('per_page', 15), 1), 50)));
    }

    public function show(Request $request, Order $order): OrderResource
    {
        Gate::authorize('viewStorefront', $order);

        return new OrderResource($order->load('items'));
    }
}
