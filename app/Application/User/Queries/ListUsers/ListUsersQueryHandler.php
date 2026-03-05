<?php

namespace App\Application\User\Queries\ListUsers;

use App\Application\User\DTOs\UserDTO;
use App\Application\User\DTOs\PagedUsersDTO;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Support\Helpers\Pagination;

class ListUsersQueryHandler
{
    public function handle(ListUsersQuery $query): PagedUsersDTO
    {
        $qb = UserModel::query();

        if ($query->search) {
            $s = mb_strtolower(trim($query->search));
            $qb->where(function ($w) use ($s) {
                $w->whereRaw('LOWER(name) LIKE ?', ["%{$s}%"])
                ->orWhereRaw('LOWER(email) LIKE ?', ["%{$s}%"]);
            });
        }

        $allowedSort = ['id', 'name', 'email', 'created_at'];
        $sortBy = in_array($query->sortBy, $allowedSort, true) ? $query->sortBy : 'id';
        $sortDir = strtolower($query->sortDir) === 'asc' ? 'asc' : 'desc';

        $p = $qb->orderBy($sortBy, $sortDir)->paginate(
            perPage: $query->perPage,
            page: $query->page
        );

        $data = [];
        foreach ($p->items() as $row) {
            $data[] = new UserDTO
            (
                $row->id, 
                $row->name, 
                $row->email
            );
        }

        return new PagedUsersDTO(
            data: $data,
            meta: Pagination::meta($p)
        );
    }
}