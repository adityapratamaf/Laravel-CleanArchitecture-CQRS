<?php

return [
    App\Providers\AppServiceProvider::class,

    App\Infrastructure\Providers\CQRSServiceProvider::class,
    App\Infrastructure\Providers\PresentationServiceProvider::class,
];
