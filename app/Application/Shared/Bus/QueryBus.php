<?php

namespace App\Application\Shared\Bus;

use Illuminate\Contracts\Container\Container;

class QueryBus
{
    public function __construct(private Container $container) {}

    public function ask(object $query): mixed
    {
        $handlerClass = get_class($query) . 'Handler';
        $handler = $this->container->make($handlerClass);

        return $handler->handle($query);
    }
}