<?php

namespace App\Application\Shared\Bus;

use Illuminate\Contracts\Container\Container;

class CommandBus
{
    public function __construct(private Container $container) {}

    public function dispatch(object $command): mixed
    {
        $handlerClass = get_class($command) . 'Handler';
        $handler = $this->container->make($handlerClass);

        return $handler->handle($command);
    }
}