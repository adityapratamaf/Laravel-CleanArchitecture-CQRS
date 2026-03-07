<?php

namespace App\Presentation\Http\Controllers\Web\Auth;

use DomainException;
use Throwable;
use App\Application\Auth\DTOs\LoginDTO;
use App\Application\Auth\Commands\LoginWeb\LoginWebCommand;
use App\Application\Shared\Bus\CommandBus;
use App\Presentation\Http\Requests\Auth\LoginWebRequest;

class LoginController
{
    public function show()
    {
        return view('auth.login');
    }

    public function store(LoginWebRequest $request, CommandBus $bus)
    {
        try {
            $dto = new LoginDTO(
                email: $request->string('email')->toString(),
                password: $request->string('password')->toString(),
                remember: (bool) $request->boolean('remember')
            );

            $bus->dispatch(new LoginWebCommand($dto));

            return redirect('/users')->with('success', 'Login sukses');
        } catch (DomainException $e) {
            return back()
                ->withInput($request->except('password'))
                ->with('error', $e->getMessage());
        } catch (Throwable $e) {
            return back()
                ->withInput($request->except('password'))
                ->with('error', 'Terjadi kesalahan pada server');
        }
    }
}