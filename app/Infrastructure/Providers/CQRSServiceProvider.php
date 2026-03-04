<?php

namespace App\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Application\Shared\Bus\CommandBus;
use App\Application\Shared\Bus\QueryBus;

use App\Domain\User\Contracts\UserRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;

class CQRSServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CommandBus::class);
        $this->app->singleton(QueryBus::class);

        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
    }
}