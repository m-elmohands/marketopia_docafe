<?php

namespace App\Actions\Payments;

use Illuminate\Http\Request;

class NormalizePaymobRedirect
{
    public function execute(Request $request): array
    {
        return [
            'amount_cents' => $this->value($request, 'amount_cents'),
            'created_at' => $this->value($request, 'created_at'),
            'currency' => $this->value($request, 'currency'),
            'error_occured' => $this->value($request, 'error_occured'),
            'has_parent_transaction' => $this->value($request, 'has_parent_transaction'),
            'id' => $this->value($request, 'id'),
            'integration_id' => $this->value($request, 'integration_id'),
            'is_3d_secure' => $this->value($request, 'is_3d_secure'),
            'is_auth' => $this->value($request, 'is_auth'),
            'is_capture' => $this->value($request, 'is_capture'),
            'is_refunded' => $this->value($request, 'is_refunded'),
            'is_standalone_payment' => $this->value($request, 'is_standalone_payment'),
            'is_voided' => $this->value($request, 'is_voided'),
            'order' => [
                'id' => $this->value($request, 'order'),
                'merchant_order_id' => $this->value($request, 'merchant_order_id'),
            ],
            'owner' => $this->value($request, 'owner'),
            'pending' => $this->value($request, 'pending'),
            'source_data' => [
                'pan' => $this->value($request, 'source_data.pan'),
                'sub_type' => $this->value($request, 'source_data.sub_type'),
                'type' => $this->value($request, 'source_data.type'),
            ],
            'success' => $this->value($request, 'success'),
            'data' => ['message' => $this->value($request, 'data.message')],
        ];
    }

    private function value(Request $request, string $key): mixed
    {
        return $request->query($key, $request->query(str_replace('.', '_', $key)));
    }
}
