<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use Laravel\Sanctum\Sanctum;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_user_via_api(): void
    {
        // bikin user dan "login" untuk sanctum
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        $res = $this->postJson('/api/users', [
            'name' => 'Adit',
            'email' => 'adit@example.com',
            'password' => 'password123',
        ]);

        $res->assertStatus(201)
            ->assertJsonStructure(['id', 'name', 'email']);

        $this->assertDatabaseHas('users', [
            'email' => 'adit@example.com',
        ]);
    }

    public function test_can_list_users_via_api(): void
    {
        // bikin user dan "login" untuk sanctum
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        // seed default user if you want
        $this->postJson('/api/users', [
            'name' => 'Adit',
            'email' => 'adit@example.com',
            'password' => 'password123',
        ]);

        $res = $this->getJson('/api/users');

        $res->assertStatus(200)
            ->assertJsonStructure([
                'data' => [['id', 'name', 'email']],
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ]);
    }

    public function test_can_show_user_via_api(): void
    {
        // bikin user dan "login" untuk sanctum
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        $create = $this->postJson('/api/users', [
            'name' => 'Adit',
            'email' => 'adit@example.com',
            'password' => 'password123',
        ])->json();

        $res = $this->getJson('/api/users/'.$create['id']);

        $res->assertStatus(200)
            ->assertJson([
                'id' => $create['id'],
                'email' => 'adit@example.com',
            ]);
    }

    public function test_can_update_user_via_api(): void
    {
        // bikin user dan "login" untuk sanctum
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        $create = $this->postJson('/api/users', [
            'name' => 'Adit',
            'email' => 'adit@example.com',
            'password' => 'password123',
        ])->json();

        $res = $this->putJson('/api/users/'.$create['id'], [
            'name' => 'Adit Updated',
            'email' => 'adit.updated@example.com',
            'password' => null,
        ]);

        $res->assertStatus(200)
            ->assertJson([
                'id' => $create['id'],
                'name' => 'Adit Updated',
                'email' => 'adit.updated@example.com',
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $create['id'],
            'email' => 'adit.updated@example.com',
        ]);
    }

    public function test_can_delete_user_via_api(): void
    {
        // bikin user dan "login" untuk sanctum
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);
        
        $create = $this->postJson('/api/users', [
            'name' => 'Adit',
            'email' => 'adit@example.com',
            'password' => 'password123',
        ])->json();

        $res = $this->deleteJson('/api/users/'.$create['id']);
        $res->assertStatus(200)->assertJson(['message' => 'User deleted']);

        $this->assertDatabaseMissing('users', ['id' => $create['id']]);
    }
}