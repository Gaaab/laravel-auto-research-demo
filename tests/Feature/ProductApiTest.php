<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

/**
 * =============================================================================
 * ProductApiTest — CORRECTNESS GUARD (DO NOT MODIFY)
 * =============================================================================
 *
 * These tests run before every benchmark. If ANY test fails, the experiment
 * is automatically invalid and discarded. This prevents the agent from
 * "gaming" the benchmark by breaking the API contract.
 *
 * The agent MUST NOT modify this file.
 * =============================================================================
 */
class ProductApiTest extends TestCase
{
    /**
     * The endpoint must return 200 OK.
     */
    public function test_products_endpoint_returns_200(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
    }

    /**
     * The response must be valid JSON with a "data" array.
     */
    public function test_response_has_data_array(): void
    {
        $response = $this->getJson('/api/products');

        $response->assertJsonStructure([
            'data' => [
                '*' => ['id', 'name', 'price', 'category'],
            ],
        ]);
    }

    /**
     * Filtering by category must work.
     */
    public function test_filter_by_category_works(): void
    {
        $response = $this->getJson('/api/products?category=electronics');

        $response->assertStatus(200);

        $data = $response->json('data');

        // All returned products must be in the "electronics" category
        foreach ($data as $product) {
            $this->assertEquals('electronics', $product['category']);
        }
    }

    /**
     * Sorting by price must work.
     */
    public function test_sort_by_price_works(): void
    {
        $response = $this->getJson('/api/products?sort=price&sort_dir=asc&per_page=20');

        $response->assertStatus(200);

        $data = $response->json('data');

        // Prices must be in ascending order
        for ($i = 1; $i < count($data); $i++) {
            $this->assertGreaterThanOrEqual(
                (float) $data[$i - 1]['price'],
                (float) $data[$i]['price'],
                'Products are not sorted by price ascending'
            );
        }
    }

    /**
     * Pagination metadata must be present.
     */
    public function test_pagination_metadata_present(): void
    {
        $response = $this->getJson('/api/products?per_page=10');

        $response->assertStatus(200);

        // Must have some form of pagination structure
        // Supports both standard paginate() and simplePaginate() / cursorPaginate()
        $json = $response->json();

        $this->assertArrayHasKey('data', $json);
        $this->assertIsArray($json['data']);
        $this->assertNotEmpty($json['data']);

        // Must have either "meta" (paginate) or "next_page_url" (simplePaginate/cursor)
        $hasMeta = isset($json['meta']) || isset($json['current_page']);
        $hasLinks = isset($json['links']) || isset($json['next_page_url']);

        $this->assertTrue(
            $hasMeta || $hasLinks,
            'Response must include pagination metadata (meta/links or next_page_url)'
        );
    }

    /**
     * The per_page parameter must be respected.
     */
    public function test_per_page_limits_results(): void
    {
        $response = $this->getJson('/api/products?per_page=5');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertLessThanOrEqual(5, count($data));
    }
}
