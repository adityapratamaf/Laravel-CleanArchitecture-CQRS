<?php

namespace App\Presentation\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Application\Shared\Bus\QueryBus;
use App\Application\Shared\Bus\CommandBus;

use App\Application\User\DTOs\CreateUserDTO;
use App\Application\User\DTOs\UpdateUserDTO;

use App\Application\User\Commands\CreateUser\CreateUserCommand;
use App\Application\User\Commands\UpdateUser\UpdateUserCommand;
use App\Application\User\Commands\DeleteUser\DeleteUserCommand;

use App\Application\User\Queries\GetUserById\GetUserByIdQuery;
use App\Application\User\Queries\ListUsers\ListUsersQuery;

use App\Presentation\Http\Requests\User\StoreUserRequest;
use App\Presentation\Http\Requests\User\UpdateUserRequest;

use App\Support\Helpers\PaginationLinks;

class UserWebController
{
    public function index(Request $request, QueryBus $queryBus)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(max((int) $request->query('per_page', 20), 1), 50);
        $sortBy = is_string($request->query('sort_by')) ? $request->query('sort_by') : 'id';
        $sortDir = is_string($request->query('sort_dir')) ? $request->query('sort_dir') : 'desc';

        $result = $queryBus->ask(new ListUsersQuery(
            page: $page,
            perPage: $perPage,
            search: is_string($request->query('search')) ? $request->query('search') : null,
            sortBy: $sortBy,
            sortDir: $sortDir
        ));

        $paginationLinks = PaginationLinks::build(
            basePath: '/users',
            query: [
                'search' => is_string($request->query('search')) ? $request->query('search') : null,
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
            currentPage: $result->meta['current_page'],
            lastPage: $result->meta['last_page'],
        );

        return view('users.index', [
            'users' => $result->data,
            'meta' => $result->meta,
            'paginationLinks' => $paginationLinks,
            'filters' => [
                'search' => (string) $request->query('search', ''),
                'per_page' => $perPage,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
            ],
        ]);
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(StoreUserRequest $request, CommandBus $commandBus)
    {
        $dto = new CreateUserDTO(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
        );

        $commandBus->dispatch(new CreateUserCommand($dto));

        return redirect('/users')->with('success', 'User created');
    }

    public function show(int $id, QueryBus $queryBus)
    {
        $user = $queryBus->ask(new GetUserByIdQuery($id));
        return view('users.show', compact('user'));
    }

    public function edit(int $id, QueryBus $queryBus)
    {
        $user = $queryBus->ask(new GetUserByIdQuery($id));
        return view('users.edit', compact('user'));
    }

    public function update(int $id, UpdateUserRequest $request, CommandBus $commandBus)
    {
        $dto = new UpdateUserDTO(
            id: $id,
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            password: $request->filled('password') ? $request->string('password')->toString() : null,
        );

        $commandBus->dispatch(new UpdateUserCommand($dto));

        return redirect('/users/'.$id)->with('success', 'User updated');
    }

    public function destroy(int $id, CommandBus $commandBus)
    {
        $commandBus->dispatch(new DeleteUserCommand($id));
        return redirect('/users')->with('success', 'User deleted');
    }
}