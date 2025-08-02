<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

describe('User Controller - Admin Users', function () {
    beforeEach(function () {
        // Run the database seeder to create roles and admin user
        $this->seed(DatabaseSeeder::class);

        // Get admin user created by seeder
        $this->admin = User::where('name', 'admin')->first();

        // Create a normal user for testing
        $this->user = User::where('name', '!=', 'admin')->first();

        // Authenticate as admin for all tests
        $this->actingAs($this->admin, 'api');
    });

    describe('GET /api/users', function () {
        it('allows admin to view list of users', function () {
            User::factory()->count(3)->create();

            $response = $this->getJson('/api/users');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'name',
                            'email',
                            'status',
                        ],
                    ],
                    'message',
                ]);
        });

        it('allows admin to filter users by name', function () {
            User::factory()->create(['name' => 'Unique John Doe']);
            User::factory()->create(['name' => 'Jane Smith']);

            $response = $this->getJson('/api/users?name=Unique John');

            $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.name', 'Unique John Doe');
        });

        it('allows admin to filter users by email', function () {
            $user1 = User::factory()->create(['email' => 'john@example.com']);
            $user2 = User::factory()->create(['email' => 'jane@example.com']);

            $response = $this->getJson('/api/users?email=john@example.com');

            $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.email', 'john@example.com');
        });

        it('allows admin to filter users by status', function () {
            User::factory()->create(['status' => UserStatus::ACTIVE]);
            User::factory()->create(['status' => UserStatus::INACTIVE]);

            $response = $this->getJson('/api/users?status='.UserStatus::ACTIVE->value);

            $response->assertStatus(200)
                ->assertJsonCount(12, 'data') // 10 from UserSeeder + 1 admin + 1 from test
                ->assertJsonPath('data.0.status', UserStatus::ACTIVE->value);
        });
    });

    describe('GET /api/users/{id}', function () {
        it('allows admin to view any user profile', function () {
            $response = $this->getJson("/api/users/{$this->user->id}");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'status',
                    ],
                    'message',
                ])
                ->assertJsonPath('data.id', $this->user->id);
        });

        it('allows admin to view their own profile', function () {
            $response = $this->getJson("/api/users/{$this->admin->id}");

            $response->assertStatus(200)
                ->assertJsonPath('data.id', $this->admin->id);
        });

        it('returns 404 for non-existent user', function () {
            $response = $this->getJson('/api/users/999');

            $response->assertStatus(404);
        });
    });

    describe('POST /api/users', function () {
        it('allows admin to create new users', function () {
            $userData = [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
            ];

            $response = $this->postJson('/api/users', $userData);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'status',
                    ],
                    'message',
                ])
                ->assertJsonPath('data.name', 'New User')
                ->assertJsonPath('data.email', 'newuser@example.com')
                ->assertJsonPath('data.status', UserStatus::ACTIVE->value);

            $this->assertDatabaseHas('users', [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'status' => UserStatus::ACTIVE->value,
            ]);
        });

        it('allows admin to create user with custom status', function () {
            $userData = [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'status' => UserStatus::PENDING->value,
            ];

            $response = $this->postJson('/api/users', $userData);

            $response->assertStatus(201)
                ->assertJsonPath('data.status', UserStatus::PENDING->value);

            $this->assertDatabaseHas('users', [
                'email' => 'newuser@example.com',
                'status' => UserStatus::PENDING->value,
            ]);
        });

        it('validates required fields', function () {
            $response = $this->postJson('/api/users', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email']);
        });

        it('validates email format', function () {
            $response = $this->postJson('/api/users', [
                'name' => 'Test User',
                'email' => 'invalid-email',
                'password' => 'password123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        it('validates unique email', function () {
            User::factory()->create(['email' => 'existing@example.com']);

            $response = $this->postJson('/api/users', [
                'name' => 'Test User',
                'email' => 'existing@example.com',
                'password' => 'password123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        it('validates password length', function () {
            $response = $this->postJson('/api/users', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => '123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });
    });

    describe('PUT /api/users/{id}', function () {
        it('allows admin to update any user', function () {
            $updateData = [
                'name' => 'Updated User',
                'email' => 'updated@example.com',
                'status' => UserStatus::INACTIVE->value,
            ];

            $response = $this->putJson("/api/users/{$this->user->id}", $updateData);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'status',
                    ],
                    'message',
                ])
                ->assertJsonPath('data.name', 'Updated User')
                ->assertJsonPath('data.email', 'updated@example.com')
                ->assertJsonPath('data.status', UserStatus::INACTIVE->value);

            $this->assertDatabaseHas('users', [
                'id' => $this->user->id,
                'name' => 'Updated User',
                'email' => 'updated@example.com',
                'status' => UserStatus::INACTIVE->value,
            ]);
        });

        it('allows admin to update user password', function () {
            $updateData = [
                'name' => $this->user->name,
                'email' => 'newemail@example.com',
                'password' => 'newpassword123',
            ];

            $response = $this->putJson("/api/users/{$this->user->id}", $updateData);

            $response->assertStatus(200);

            $this->assertDatabaseHas('users', [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => 'newemail@example.com',
            ]);

            // Check that password was hashed
            $updatedUser = User::find($this->user->id);
            expect(Hash::check('newpassword123', $updatedUser->password))->toBeTrue();
        });

        it('returns 404 for non-existent user', function () {
            $response = $this->putJson('/api/users/999', [
                'name' => 'Test',
                'email' => 'test@example.com',
            ]);

            $response->assertStatus(404);
        });

        it('validates required fields', function () {
            $response = $this->putJson("/api/users/{$this->user->id}", []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email']);
        });

        it('validates email uniqueness excluding current user', function () {
            $otherUser = User::factory()->create(['email' => 'other@example.com']);

            $response = $this->putJson("/api/users/{$this->user->id}", [
                'name' => 'Test User',
                'email' => 'other@example.com',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });
    });

    describe('DELETE /api/users/{id}', function () {
        it('allows admin to delete any user', function () {
            $response = $this->deleteJson("/api/users/{$this->user->id}");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'status',
                    ],
                    'message',
                ]);

            $this->assertDatabaseMissing('users', [
                'id' => $this->user->id,
            ]);
        });

        it('returns 404 for non-existent user', function () {
            $response = $this->deleteJson('/api/users/999');

            $response->assertStatus(404);
        });
    });
});

describe('User Controller - Normal Users', function () {
    beforeEach(function () {
        // Run the database seeder to create roles and admin user
        $this->seed(DatabaseSeeder::class);

        // Get admin user created by seeder
        $this->admin = User::where('name', 'admin')->first();

        // Create a normal user for testing
        $this->user = User::where('name', '!=', 'admin')->first();

        // Authenticate as normal user for all tests
        $this->actingAs($this->user, 'api');
    });

    describe('GET /api/users', function () {
        it('denies normal users from viewing list of users', function () {
            $response = $this->getJson('/api/users');

            $response->assertStatus(403);
        });
    });

    describe('GET /api/users/{id}', function () {
        it('allows normal users to view their own profile', function () {
            $response = $this->getJson("/api/users/{$this->user->id}");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'name',
                        'email',
                        'status',
                    ],
                    'message',
                ])
                ->assertJsonPath('data.id', $this->user->id);
        });

        it('denies normal users from viewing other users profiles', function () {
            $response = $this->getJson("/api/users/{$this->admin->id}");

            $response->assertStatus(403);
        });

        it('returns 404 for non-existent user', function () {
            $response = $this->getJson('/api/users/999');

            $response->assertStatus(404);
        });
    });

    describe('POST /api/users', function () {
        it('denies normal users from creating new users', function () {
            $userData = [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
            ];

            $response = $this->postJson('/api/users', $userData);

            $response->assertStatus(403);
        });
    });

    describe('PUT /api/users/{id}', function () {
        it('allows normal users to update their own profile', function () {
            $updateData = [
                'name' => 'Updated Normal User',
                'email' => 'updated@example.com',
                'status' => UserStatus::INACTIVE->value,
            ];

            $response = $this->putJson("/api/users/{$this->user->id}", $updateData);

            $response->assertStatus(200)
                ->assertJsonPath('data.name', 'Updated Normal User')
                ->assertJsonPath('data.email', 'updated@example.com');

            $this->assertDatabaseHas('users', [
                'id' => $this->user->id,
                'name' => 'Updated Normal User',
                'email' => 'updated@example.com',
            ]);
        });

        it('denies normal users from updating other users profiles', function () {
            $updateData = [
                'name' => 'Updated Admin',
                'email' => 'admin@example.com',
                'status' => UserStatus::INACTIVE->value,
            ];

            $response = $this->putJson("/api/users/{$this->admin->id}", $updateData);

            $response->assertStatus(403);
        });

        it('validates required fields for normal users', function () {
            $response = $this->putJson("/api/users/{$this->user->id}", []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['name', 'email']);
        });
    });

    describe('DELETE /api/users/{id}', function () {
        it('denies normal users from deleting users', function () {
            $response = $this->deleteJson("/api/users/{$this->user->id}");

            $response->assertStatus(403);
        });

        it('returns 404 for non-existent user', function () {
            $response = $this->deleteJson('/api/users/999');

            $response->assertStatus(404);
        });
    });
});
