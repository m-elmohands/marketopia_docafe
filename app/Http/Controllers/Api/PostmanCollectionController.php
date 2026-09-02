<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class PostmanCollectionController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $collection = json_decode(
            file_get_contents(base_path('docs/api/do-cafe.postman_collection.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $collection['variable'] = collect($collection['variable'] ?? [])
            ->map(function (array $variable): array {
                if (($variable['key'] ?? null) === 'base_url') {
                    $variable['value'] = rtrim((string) config('app.url'), '/').'/api/v1';
                }

                return $variable;
            })
            ->all();

        return response()
            ->json($collection, headers: ['Content-Disposition' => 'inline'])
            ->setEncodingOptions(JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}
