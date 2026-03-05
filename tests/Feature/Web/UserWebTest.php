<?php

namespace Tests\Feature\Web;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

class UserWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_page_loads(): void
    {
        UserModel::create([
            'name' => 'Adit',
            'email' => 'adit@example.com',
            'password' => bcrypt('password123'),
        ]);

        $res = $this->get('/users');

        $res->assertStatus(200);
        $res->assertSee('Users');
        $res->assertSee('adit@example.com');
    }

    public function test_create_user_form_loads(): void
    {
        $res = $this->get('/users/create');
        $res->assertStatus(200)->assertSee('Create User');
    }
}