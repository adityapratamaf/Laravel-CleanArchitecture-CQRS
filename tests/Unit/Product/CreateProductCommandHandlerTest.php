<?php

namespace Tests\Unit\Product;

use Tests\TestCase;
use Mockery;
use App\Domain\Product\Contracts\ProductRepository;
use App\Application\Product\DTOs\CreateProductDTO;
use App\Application\Product\Commands\CreateProduct\CreateProductCommand;
use App\Application\Product\Commands\CreateProduct\CreateProductCommandHandler;
use App\Domain\Product\Entities\Product;

class CreateProductCommandHandlerTest extends TestCase
{
    public function test_create_product_handler_returns_product_dto(): void
    {
        $repo = Mockery::mock(ProductRepository::class);

        $repo->shouldReceive('findBySku')
            ->once()
            ->with('SKU-KB-001')
            ->andReturn(null);

        $repo->shouldReceive('create')
            ->once()
            ->andReturn(new Product(
                id: 1,
                name: 'Keyboard',
                sku: 'SKU-KB-001',
                price: 150000.0,
                stock: 10,
                description: 'test'
            ));

        $handler = new CreateProductCommandHandler($repo);

        $dto = new CreateProductDTO('Keyboard', 'SKU-KB-001', 150000.0, 10, 'test');
        $result = $handler->handle(new CreateProductCommand($dto));

        $this->assertSame(1, $result->id);
        $this->assertSame('SKU-KB-001', $result->sku);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}