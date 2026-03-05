<?php

namespace Tests\Feature\Web;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;

class ProductWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_page_loads(): void
    {
        ProductModel::create([
            'name' => 'Keyboard',
            'sku' => 'SKU-KB-001',
            'price' => 150000,
            'stock' => 10,
            'description' => 'test',
        ]);

        $res = $this->get('/products');

        $res->assertStatus(200);
        $res->assertSee('Products');
        $res->assertSee('SKU-KB-001');
    }

    public function test_create_product_form_loads(): void
    {
        $res = $this->get('/products/create');
        $res->assertStatus(200)->assertSee('Create Product');
    }
}