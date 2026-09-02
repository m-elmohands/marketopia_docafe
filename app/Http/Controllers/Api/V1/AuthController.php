<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Models\User;
use App\Support\Api\RespondsWithApiEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    use RespondsWithApiEnvelope;

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->safe()->only(['name', 'email', 'password']));

        return $this->tokenResponse($user, $request->string('device_name')->toString(), 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::query()->where('email', $request->string('email'))->first();

        if (! $user || ! Hash::check($request->string('password'), $user->password)) {
            throw ValidationException::withMessages(['email' => [__('The provided credentials are incorrect.')]]);
        }

        return $this->tokenResponse($user, $request->string('device_name')->toString());
    }

    public function user(Request $request): JsonResponse
    {
        return $this->success($this->userData($request->user()));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return $this->success(message: __('Logged out successfully.'));
    }

    private function tokenResponse(User $user, string $deviceName, int $status = 200): JsonResponse
    {
        return $this->success([
            'user' => $this->userData($user),
            'token' => $user->createToken($deviceName, ['storefront'])->plainTextToken,
            'token_type' => 'Bearer',
        ], __('Authenticated successfully.'), $status);
    }

    private function userData(User $user): array
    {
        return ['id' => $user->id, 'name' => $user->name, 'email' => $user->email];
    }
}
