<?php

namespace App\Actions\Address;

use App\Models\CustomerAddress;
use Illuminate\Support\Facades\DB;

class DeleteCustomerAddress
{
    public function handle(CustomerAddress $address): void
    {
        DB::transaction(function () use ($address): void {
            $wasDefault = $address->is_default;
            $user = $address->user;
            $address->delete();

            if ($wasDefault) {
                $user->addresses()->oldest()->first()?->update(['is_default' => true]);
            }
        });
    }
}
