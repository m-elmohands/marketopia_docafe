<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\Api\RespondsWithApiEnvelope;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    use RespondsWithApiEnvelope;

    public function index(): JsonResponse
    {
        $settings = Cache::remember('api.settings.'.app()->getLocale(), now()->addHour(), fn () => Setting::query()
            ->with('translations')
            ->where('is_public', true)
            ->get()
            ->mapWithKeys(fn (Setting $setting): array => [$setting->key => $setting->translate(app()->getLocale(), true)?->value]));

        return $this->success($settings);
    }
}
