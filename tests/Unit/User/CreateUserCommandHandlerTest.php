<?php

namespace Tests\Unit\User;

use Tests\TestCase;
use Mockery;
use App\Domain\User\Contracts\UserRepository;
use App\Application\User\DTOs\CreateUserDTO;
use App\Application\User\Commands\CreateUser\CreateUserCommand;
use App\Application\User\Commands\CreateUser\CreateUserCommandHandler;
use App\Domain\User\Entities\User;

class CreateUserCommandHandlerTest extends TestCase
{
    public function test_create_user_handler_returns_user_dto(): void
    {
        $repo = Mockery::mock(UserRepository::class);

        $repo->shouldReceive('findByEmail')
            ->once()
            ->with('adit@example.com')
            ->andReturn(null);

        $repo->shouldReceive('create')
            ->once()
            ->andReturn(new User(
                id: 1,
                name: 'Adit',
                email: 'adit@example.com',
                passwordHash: 'hashed'
            ));

        $handler = new CreateUserCommandHandler($repo);

        $dto = new CreateUserDTO('Adit', 'adit@example.com', 'password123');
        $result = $handler->handle(new CreateUserCommand($dto));

        $this->assertSame(1, $result->id);
        $this->assertSame('adit@example.com', $result->email);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}