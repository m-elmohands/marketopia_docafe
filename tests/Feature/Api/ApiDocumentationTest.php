<?php

namespace Tests\Feature\Api;

use Tests\TestCase;

class ApiDocumentationTest extends TestCase
{
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
    }
}
