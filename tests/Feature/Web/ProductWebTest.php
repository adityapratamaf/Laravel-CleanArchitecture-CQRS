<?php

namespace Tests\Feature\Web;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Support\Facades\DB;

class ProductWebTest extends TestCase
{
    use RefreshDatabase;

    private function login(): void
    {
        $user = UserModel::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->actingAs($user);
    }

    public function test_products_page_loads(): void
    {
        $this->login();

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
        $this->login();

        $res = $this->get('/products/create');
        $res->assertStatus(200)->assertSee('Create Product');
    }
}