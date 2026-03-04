<?php

namespace App\Application\Product\Queries\ListProducts;

use App\Application\Product\DTOs\ProductDTO;
use App\Application\Product\DTOs\PagedProductsDTO;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Support\Helpers\Pagination;

class ListProductsQueryHandler
{
    public function handle(ListProductsQuery $query): PagedProductsDTO
    {
        $qb = ProductModel::query();

        if ($query->search) {
            $s = mb_strtolower(trim($query->search));
            $qb->where(function ($w) use ($s) {
                $w->whereRaw('LOWER(name) LIKE ?', ["%{$s}%"])
                ->orWhereRaw('LOWER(sku) LIKE ?', ["%{$s}%"]);
            });
        }

        $allowedSort = ['id', 'name', 'sku', 'price', 'stock', 'created_at'];
        $sortBy = in_array($query->sortBy, $allowedSort, true) ? $query->sortBy : 'id';
        $sortDir = strtolower($query->sortDir) === 'asc' ? 'asc' : 'desc';

        $p = $qb->orderBy($sortBy, $sortDir)->paginate(
            perPage: $query->perPage,
            page: $query->page
        );

        $data = [];
        foreach ($p->items() as $row) {
            $data[] = new ProductDTO(
                $row->id,
                $row->name,
                $row->sku,
                (float) $row->price,
                (int) $row->stock,
                $row->description
            );
        }

        return new PagedProductsDTO(
            data: $data,
            meta: Pagination::meta($p)
        );
    }
}