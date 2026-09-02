<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Payments\ProcessPaymobWebhook;
use App\Http\Controllers\Controller;
use App\Support\Api\RespondsWithApiEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymobWebhookController extends Controller
{
    use RespondsWithApiEnvelope;

    public function __invoke(Request $request, ProcessPaymobWebhook $processor): JsonResponse
    {
        $webhook = $processor->execute($request->all(), $request->query('hmac'));

        if (! $webhook->hmac_verified) {
            return $this->success(message: __('Invalid payment signature.'), status: 401);
        }

        return $this->success(['status' => $webhook->status], __('Webhook accepted.'));
    }
}
