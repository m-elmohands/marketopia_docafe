<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Profile\UpdateCustomerProfile;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\UpdateProfileRequest;
use App\Http\Resources\Api\V1\UserResource;
use App\Support\Api\RespondsWithApiEnvelope;
use Illuminate\Http\JsonResponse;

class ProfileController extends Controller
{
    use RespondsWithApiEnvelope;

    public function update(UpdateProfileRequest $request, UpdateCustomerProfile $action): JsonResponse
    {
        $user = $action->handle($request->user(), $request->validated());

        return $this->success(
            (new UserResource($user))->resolve($request),
            __('Profile updated successfully.'),
        );
    }
}
