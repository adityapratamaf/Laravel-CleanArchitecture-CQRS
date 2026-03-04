<?php

namespace App\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use App\Application\Shared\Bus\CommandBus;
use App\Application\Shared\Bus\QueryBus;

use App\Domain\User\Contracts\UserRepository;
use App\Domain\Product\Contracts\ProductRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;

class CQRSServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CommandBus::class);
        $this->app->singleton(QueryBus::class);

        // Register Binding Repository
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(ProductRepository::class, EloquentProductRepository::class);
    }
}