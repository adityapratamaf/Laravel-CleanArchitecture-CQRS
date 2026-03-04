<?php

namespace App\Application\Product\Commands\DeleteProduct;

use App\Domain\Product\Contracts\ProductRepository;

class DeleteProductCommandHandler
{
    public function __construct(private ProductRepository $products) {}

    public function handle(DeleteProductCommand $command): void
    {
        $this->products->delete($command->id);
    }
}