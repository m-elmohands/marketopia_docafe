<?php

namespace Tests\Feature\Api;

use Illuminate\Support\Facades\Route;
use Symfony\Component\Yaml\Yaml;
use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
    public function test_every_versioned_api_route_is_documented_in_openapi(): void
    {
        $paths = Yaml::parseFile(base_path('docs/api/openapi.yaml'))['paths'];
        $missingOperations = [];

        foreach (Route::getRoutes() as $route) {
            if (! str_starts_with($route->uri(), 'api/v1')) {
                continue;
            }

            $path = '/'.preg_replace('#^api/v1/?#', '', $route->uri());

            foreach (array_diff($route->methods(), ['HEAD']) as $method) {
                if (! isset($paths[$path][strtolower($method)])) {
                    $missingOperations[] = $method.' '.$path;
                }
            }
        }

        $this->assertSame([], $missingOperations, 'OpenAPI is missing: '.implode(', ', $missingOperations));
    }

    public function test_interactive_api_documentation_is_available(): void
    {
        $this->get('/api/documentation')
            ->assertOk()
            ->assertSee('Do Cafe API Documentation')
            ->assertSee('swagger-ui')
            ->assertSee('openapi.yaml')
            ->assertSee(route('api.postman'), false);
    }

    public function test_raw_openapi_specification_is_available(): void
    {
        $response = $this->get('/api/openapi.yaml')
            ->assertOk()
            ->assertHeader('content-type', 'application/yaml; charset=UTF-8');

        $this->assertSame(
            realpath(base_path('docs/api/openapi.yaml')),
            $response->baseResponse->getFile()->getRealPath(),
        );

        $specification = file_get_contents(base_path('docs/api/openapi.yaml'));
        $this->assertStringContainsString('/profile:', $specification);
        $this->assertStringContainsString('/addresses:', $specification);
        $this->assertStringContainsString('/wishlist:', $specification);
        $this->assertStringContainsString('cash_on_delivery', $specification);
        $this->assertStringContainsString('address_id', $specification);
    }

    public function test_postman_collection_is_served_inline_as_valid_json(): void
    {
        config()->set('app.url', 'https://cafe.example.com/');

        $response = $this->getJson('/api/postman-collection')
            ->assertOk()
            ->assertHeader('content-type', 'application/json')
            ->assertHeader('content-disposition', 'inline');

        $collection = $response->json();

        $this->assertSame('Do Cafe Ecommerce API', $collection['info']['name']);
        $this->assertNotEmpty($collection['item']);
        $this->assertSame(
            'https://cafe.example.com/api/v1',
            collect($collection['variable'])->firstWhere('key', 'base_url')['value'],
        );

        $requests = collect($collection['item'])
            ->flatMap(fn (array $folder): array => $folder['item'] ?? [])
            ->pluck('request.url');

        $this->assertTrue($requests->contains('{{base_url}}/profile'));
        $this->assertTrue($requests->contains('{{base_url}}/addresses'));
        $this->assertTrue($requests->contains('{{base_url}}/addresses/{{address_id}}'));
        $this->assertTrue($requests->contains('{{base_url}}/wishlist?sort=newest&per_page=15'));

        $serializedCollection = json_encode($collection, JSON_THROW_ON_ERROR);
        $this->assertStringContainsString('cash_on_delivery', $serializedCollection);
        $this->assertStringContainsString('address_id', $serializedCollection);
    }
}
