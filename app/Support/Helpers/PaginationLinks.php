<?php

namespace App\Support\Helpers;

class PaginationLinks
{
    /**
     * Build pagination links as array of items: ['label' => '1', 'url' => '/users?page=1', 'active' => true]
     */
    public static function build(
        string $basePath,
        array $query,
        int $currentPage,
        int $lastPage,
        int $window = 2
    ): array {
        $items = [];

        $makeUrl = function (int $page) use ($basePath, $query): string {
            $query['page'] = $page;
            return $basePath . '?' . http_build_query($query);
        };

        // Prev
        $items[] = [
            'label' => 'Prev',
            'url' => $currentPage > 1 ? $makeUrl($currentPage - 1) : null,
            'active' => false,
            'disabled' => $currentPage <= 1,
        ];

        // Window pages
        $start = max(1, $currentPage - $window);
        $end   = min($lastPage, $currentPage + $window);

        // First + ellipsis
        if ($start > 1) {
            $items[] = ['label' => '1', 'url' => $makeUrl(1), 'active' => $currentPage === 1, 'disabled' => false];
            if ($start > 2) {
                $items[] = ['label' => '...', 'url' => null, 'active' => false, 'disabled' => true];
            }
        }

        for ($p = $start; $p <= $end; $p++) {
            $items[] = [
                'label' => (string) $p,
                'url' => $makeUrl($p),
                'active' => $p === $currentPage,
                'disabled' => false,
            ];
        }

        // Last + ellipsis
        if ($end < $lastPage) {
            if ($end < $lastPage - 1) {
                $items[] = ['label' => '...', 'url' => null, 'active' => false, 'disabled' => true];
            }
            $items[] = [
                'label' => (string) $lastPage,
                'url' => $makeUrl($lastPage),
                'active' => $currentPage === $lastPage,
                'disabled' => false,
            ];
        }

        // Next
        $items[] = [
            'label' => 'Next',
            'url' => $currentPage < $lastPage ? $makeUrl($currentPage + 1) : null,
            'active' => false,
            'disabled' => $currentPage >= $lastPage,
        ];

        return $items;
    }
}