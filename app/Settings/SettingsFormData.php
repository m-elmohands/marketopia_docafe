<?php

namespace App\Settings;

use App\Enums\SettingType;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingsFormData
{
    /** @return array<string, mixed> */
    public function load(): array
    {
        $settings = Setting::query()->with('translations')->get()->keyBy('key');

        return [
            'identity' => [
                'en' => [
                    'name' => $settings->get('cafe_name')?->translate('en')?->value,
                    'description' => $settings->get('cafe_description')?->translate('en')?->value,
                ],
                'ar' => [
                    'name' => $settings->get('cafe_name')?->translate('ar')?->value,
                    'description' => $settings->get('cafe_description')?->translate('ar')?->value,
                ],
            ],
            'contact' => [
                'email' => $settings->get('contact_email')?->translate('en')?->value,
                'phone' => $settings->get('contact_phone')?->translate('en')?->value,
                'en_address' => $settings->get('address')?->translate('en')?->value,
                'ar_address' => $settings->get('address')?->translate('ar')?->value,
            ],
            'commerce' => [
                'delivery_fee' => $settings->get('delivery_fee')?->translate('en')?->value,
                'tax_percentage' => $settings->get('tax_percentage')?->translate('en')?->value,
            ],
            'social' => [
                'facebook_url' => $settings->get('facebook_url')?->translate('en')?->value,
                'instagram_url' => $settings->get('instagram_url')?->translate('en')?->value,
            ],
        ];
    }

    /** @param array<string, mixed> $data */
    public function save(array $data): void
    {
        DB::transaction(function () use ($data): void {
            $this->store('cafe_name', 'general', SettingType::String, true, [
                'en' => data_get($data, 'identity.en.name'),
                'ar' => data_get($data, 'identity.ar.name'),
            ]);
            $this->store('cafe_description', 'general', SettingType::String, true, [
                'en' => data_get($data, 'identity.en.description'),
                'ar' => data_get($data, 'identity.ar.description'),
            ]);
            $this->store('address', 'contact', SettingType::String, true, [
                'en' => data_get($data, 'contact.en_address'),
                'ar' => data_get($data, 'contact.ar_address'),
            ]);

            foreach ([
                'contact_email' => ['contact', SettingType::String, data_get($data, 'contact.email')],
                'contact_phone' => ['contact', SettingType::String, data_get($data, 'contact.phone')],
                'delivery_fee' => ['commerce', SettingType::Integer, data_get($data, 'commerce.delivery_fee')],
                'tax_percentage' => ['commerce', SettingType::Integer, data_get($data, 'commerce.tax_percentage')],
                'facebook_url' => ['social', SettingType::String, data_get($data, 'social.facebook_url')],
                'instagram_url' => ['social', SettingType::String, data_get($data, 'social.instagram_url')],
            ] as $key => [$group, $type, $value]) {
                $this->store($key, $group, $type, true, ['en' => $value, 'ar' => $value]);
            }
        });

        Cache::forget('settings.public');
    }

    /** @param array{en: mixed, ar: mixed} $values */
    private function store(string $key, string $group, SettingType $type, bool $isPublic, array $values): void
    {
        $setting = Setting::query()->firstOrNew(['key' => $key]);
        $setting->fill(['group' => $group, 'type' => $type, 'is_public' => $isPublic]);

        foreach ($values as $locale => $value) {
            $setting->translateOrNew($locale)->value = $value === null ? null : (string) $value;
        }

        $setting->save();
    }
}
