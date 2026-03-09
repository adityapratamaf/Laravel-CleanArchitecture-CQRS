<?php

namespace App\Application\Product\Commands\DeleteProduct;

use App\Domain\Product\Contracts\ProductRepository;
use App\Support\Helpers\FileUpload;

class DeleteProductCommandHandler
{
    public function __construct(private ProductRepository $products) {}

    public function handle(DeleteProductCommand $command): void
    {
        $product = $this->products->findById($command->id);

        if (!$product) {
            throw new \DomainException('Product not found');
        }

        if ($product->image) {
            FileUpload::deletePublic($product->image);
        }

        $this->products->delete($command->id);
    }
}