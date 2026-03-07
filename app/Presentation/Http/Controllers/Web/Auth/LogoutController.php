<?php

namespace App\Presentation\Http\Controllers\Web\Auth;

use DomainException;
use App\Application\Auth\Commands\LogoutWeb\LogoutWebCommand;
use App\Application\Shared\Bus\CommandBus;

class LogoutController
{
    public function __invoke(CommandBus $bus)
    {
        try {
            $bus->dispatch(new LogoutWebCommand());

            return redirect('/login')->with('success', 'Logout sukses');
        } catch (DomainException $e) {
            return redirect('/login')->with('error', $e->getMessage());
        } catch (\Throwable $e) {
            return redirect('/login')->with('error', 'Terjadi kesalahan pada server');
        }
    }
}