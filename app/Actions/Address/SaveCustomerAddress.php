<?php

namespace App\Actions\Address;

use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SaveCustomerAddress
{
    public function handle(User $user, array $data, ?CustomerAddress $address = null): CustomerAddress
    {
        return DB::transaction(function () use ($user, $data, $address): CustomerAddress {
            $address ??= new CustomerAddress(['user_id' => $user->id]);

            $shouldBeDefault = (bool) ($data['is_default'] ?? false)
                || ! $user->addresses()->exists()
                || ($address->exists && $address->is_default && array_key_exists('is_default', $data));

            if ($shouldBeDefault) {
                $user->addresses()->whereKeyNot($address->getKey())->update(['is_default' => false]);
                $data['is_default'] = true;
            }

            $address->fill($data)->save();

            return $address->refresh();
        });
    }
}
