<?php

namespace App\Presentation\Http\Controllers\Api\Auth;

use App\Application\Auth\DTOs\LoginDTO;
use App\Application\Auth\Commands\LoginApi\LoginApiCommand;
use App\Application\Auth\Commands\LogoutApi\LogoutApiCommand;
use App\Application\Shared\Bus\CommandBus;
use App\Presentation\Http\Requests\Auth\LoginApiRequest;

class AuthApiController
{
    public function login(LoginApiRequest $request, CommandBus $bus)
    {
        $dto = new LoginDTO(
            email: $request->string('email')->toString(),
            password: $request->string('password')->toString(),
            remember: false
        );

        $device = $request->filled('device_name')
            ? $request->string('device_name')->toString()
            : 'api';

        $tokenDto = $bus->dispatch(new LoginApiCommand($dto, $device));

        return response()->json([
            'token_type' => $tokenDto->token_type,
            'token' => $tokenDto->token,
        ]);
    }

    public function logout(CommandBus $bus)
    {
        $bus->dispatch(new LogoutApiCommand());
        return response()->json(['message' => 'Logged out']);
    }
}