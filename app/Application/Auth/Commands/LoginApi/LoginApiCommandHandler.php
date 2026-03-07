<?php

namespace App\Application\Auth\Commands\LoginApi;

use App\Application\Auth\DTOs\ApiTokenDTO;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Support\Facades\Hash;

class LoginApiCommandHandler
{
    public function handle(LoginApiCommand $command): ApiTokenDTO
    {
        $data = $command->data;

        $user = UserModel::query()->where('email', $data->email)->first();
        if (!$user || !Hash::check($data->password, $user->password)) {
            throw new \DomainException('Invalid credentials');
        }

        $token = $user->createToken($command->deviceName)->plainTextToken;

        return new ApiTokenDTO($token);
    }
}