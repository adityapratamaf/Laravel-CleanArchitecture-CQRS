<?php

namespace App\Support\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;

class Pagination
{
    public static function meta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'per_page'     => $paginator->perPage(),
            'count'        => $paginator->count(),
            'total'        => $paginator->total(),
            'last_page'    => $paginator->lastPage(),
        ];
    }
}