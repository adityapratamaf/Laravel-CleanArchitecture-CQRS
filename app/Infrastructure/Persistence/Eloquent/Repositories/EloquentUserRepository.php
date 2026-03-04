<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\User\Contracts\UserRepository;
use App\Domain\User\Entities\User;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

class EloquentUserRepository implements UserRepository
{
    public function create(User $user): User
    {
        $row = UserModel::create([
            'name' => $user->name,
            'email' => $user->email,
            'password' => $user->passwordHash,
        ]);

        return new User($row->id, $row->name, $row->email, $row->password);
    }

    public function update(User $user): User
    {
        $row = UserModel::findOrFail($user->id);
        $row->name = $user->name;
        $row->email = $user->email;
        $row->password = $user->passwordHash;
        $row->save();

        return new User($row->id, $row->name, $row->email, $row->password);
    }

    public function delete(int $id): void
    {
        UserModel::query()->where('id', $id)->delete();
    }

    public function findById(int $id): ?User
    {
        $row = UserModel::find($id);
        if (!$row) return null;

        return new User($row->id, $row->name, $row->email, $row->password);
    }

    public function findByEmail(string $email): ?User
    {
        $row = UserModel::query()->where('email', $email)->first();
        if (!$row) return null;

        return new User($row->id, $row->name, $row->email, $row->password);
    }
}