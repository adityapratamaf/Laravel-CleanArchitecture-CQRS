<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\ProductModelFactory;

class ProductModel extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'name', 'sku', 'price', 'stock', 'description', 'image'
    ];

    protected static function newFactory()
    {
        return ProductModelFactory::new();
    }
}