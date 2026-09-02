<?php

namespace App\Http\Controllers;

use App\Actions\Payments\NormalizePaymobRedirect;
use App\Actions\Payments\ProcessPaymobWebhook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentResultController extends Controller
{
    public function __invoke(
        Request $request,
        NormalizePaymobRedirect $normalizer,
        ProcessPaymobWebhook $processor,
    ): Response {
        $object = $normalizer->execute($request);
        $webhook = $processor->execute(
            ['type' => 'TRANSACTION_REDIRECT', 'obj' => $object],
            $request->query('hmac'),
        );

        $order = $webhook->transaction?->intention?->order;
        $verified = $webhook->hmac_verified;

        return response()->view('payments.result', [
            'order' => $order,
            'verified' => $verified,
            'successful' => $verified && $order?->payment_status->value === 'paid',
            'pending' => $verified && $order?->payment_status->value === 'pending',
        ], $verified ? 200 : 400);
    }
}
