<?php

namespace App\Presentation\Http\Controllers\Web\Auth;

use App\Application\Auth\Commands\LogoutWeb\LogoutWebCommand;
use App\Application\Shared\Bus\CommandBus;

class LogoutController
{
    public function __invoke(CommandBus $bus)
    {
        $bus->dispatch(new LogoutWebCommand());
        return redirect('/login')->with('success', 'Logout sukses');
    }
}