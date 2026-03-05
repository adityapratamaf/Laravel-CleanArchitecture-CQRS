<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\UserModelFactory;

class UserModel extends Authenticatable
{
    use HasFactory;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected static function newFactory()
    {
        return UserModelFactory::new();
    }
}