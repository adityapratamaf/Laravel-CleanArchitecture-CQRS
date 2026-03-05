<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_product_via_api(): void
    {
        $res = $this->postJson('/api/products', [
            'name' => 'Keyboard',
            'sku' => 'SKU-KB-001',
            'price' => 150000,
            'stock' => 10,
            'description' => 'Mechanical keyboard',
        ]);

        $res->assertStatus(201)
            ->assertJsonStructure(['id','name','sku','price','stock','description']);

        $this->assertDatabaseHas('products', ['sku' => 'SKU-KB-001']);
    }

    public function test_can_list_products_via_api(): void
    {
        $this->postJson('/api/products', [
            'name' => 'Mouse',
            'sku' => 'SKU-MS-001',
            'price' => 50000,
            'stock' => 30,
        ]);

        $res = $this->getJson('/api/products');

        $res->assertStatus(200)
            ->assertJsonStructure([
                'data' => [[
                    'id','name','sku','price','stock','description'
                ]],
                'meta' => ['current_page','per_page','total','last_page'],
            ]);
    }

    public function test_can_show_product_via_api(): void
    {
        $create = $this->postJson('/api/products', [
            'name' => 'Monitor',
            'sku' => 'SKU-MN-001',
            'price' => 2000000,
            'stock' => 5,
        ])->json();

        $res = $this->getJson('/api/products/'.$create['id']);

        $res->assertStatus(200)
            ->assertJson([
                'id' => $create['id'],
                'sku' => 'SKU-MN-001',
            ]);
    }

    public function test_can_update_product_via_api(): void
    {
        $create = $this->postJson('/api/products', [
            'name' => 'Monitor',
            'sku' => 'SKU-MN-001',
            'price' => 2000000,
            'stock' => 5,
        ])->json();

        $res = $this->putJson('/api/products/'.$create['id'], [
            'name' => 'Monitor 27"',
            'sku' => 'SKU-MN-001',
            'price' => 2500000,
            'stock' => 7,
            'description' => 'Updated',
        ]);

        $res->assertStatus(200)
            ->assertJson([
                'id' => $create['id'],
                'name' => 'Monitor 27"',
                'stock' => 7,
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $create['id'],
            'stock' => 7,
        ]);
    }

    public function test_can_delete_product_via_api(): void
    {
        $create = $this->postJson('/api/products', [
            'name' => 'Monitor',
            'sku' => 'SKU-MN-001',
            'price' => 2000000,
            'stock' => 5,
        ])->json();

        $res = $this->deleteJson('/api/products/'.$create['id']);
        $res->assertStatus(200)->assertJson(['message' => 'Product deleted']);

        $this->assertDatabaseMissing('products', ['id' => $create['id']]);
    }
}