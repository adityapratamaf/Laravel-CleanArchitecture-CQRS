<?php

namespace App\Presentation\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Application\Shared\Bus\CommandBus;
use App\Application\Shared\Bus\QueryBus;

use App\Application\User\DTOs\CreateUserDTO;
use App\Application\User\DTOs\UpdateUserDTO;

use App\Application\User\Commands\CreateUser\CreateUserCommand;
use App\Application\User\Commands\UpdateUser\UpdateUserCommand;
use App\Application\User\Commands\DeleteUser\DeleteUserCommand;

use App\Application\User\Queries\GetUserById\GetUserByIdQuery;
use App\Application\User\Queries\ListUsers\ListUsersQuery;

use App\Presentation\Http\Requests\User\StoreUserRequest;
use App\Presentation\Http\Requests\User\UpdateUserRequest;

class UserApiController
{
    public function index(Request $request, QueryBus $queryBus)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = min(max((int) $request->query('per_page', 20), 1), 100);
        
        $result = $queryBus->ask(new ListUsersQuery(
            page: $page,
            perPage: $perPage,
            search: is_string($request->query('search')) ? $request->query('search') : null,
            sortBy: is_string($request->query('sort_by')) ? $request->query('sort_by') : 'id',
            sortDir: is_string($request->query('sort_dir')) ? $request->query('sort_dir') : 'desc',
        ));

        $start = (($result->meta['current_page'] - 1) * $result->meta['per_page']) + 1;

        return response()->json([
            'data' => array_map(function($dto) use (&$start) {
                return [
                    'no' => $start++,
                    'id' => $dto->id,
                    'name' => $dto->name,
                    'email' => $dto->email,
                ];
            }, $result->data),
            'meta' => $result->meta,
        ]);
    }

    public function store(StoreUserRequest $request, CommandBus $commandBus)
    {
        $dto = new CreateUserDTO(
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
        );

        $user = $commandBus->dispatch(new CreateUserCommand($dto));

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ], 201);
    }

    public function show(int $id, QueryBus $queryBus)
    {
        $user = $queryBus->ask(new GetUserByIdQuery($id));

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    public function update(int $id, UpdateUserRequest $request, CommandBus $commandBus)
    {
        $dto = new UpdateUserDTO(
            id: $id,
            name: $request->string('name')->toString(),
            email: $request->string('email')->toString(),
            password: $request->filled('password') ? $request->string('password')->toString() : null,
        );

        $user = $commandBus->dispatch(new UpdateUserCommand($dto));

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]);
    }

    public function destroy(int $id, CommandBus $commandBus)
    {
        $commandBus->dispatch(new DeleteUserCommand($id));

        return response()->json(['message' => 'User deleted']);
    }
}