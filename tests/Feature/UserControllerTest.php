<?php

declare(strict_types=1);

use App\Models\User;
use App\Enums\UserStatus;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User Controller - Admin Users', function () {
    beforeEach(function () {
        // Run the database seeder to create roles and admin user
        $this->seed(DatabaseSeeder::class);

        // Get admin user created by seeder
        $this->admin = User::where('username', 'admin')->first();

        // Create a normal user for testing
        $this->user = User::where('username', '!=', 'admin')->first();

        // Authenticate as admin for all tests
        $this->actingAs($this->admin, 'api');
    });

    describe('GET /api/v1/users', function () {
        it('allows admin to view list of users', function () {
            User::factory()->count(3)->create();

            $response = $this->getJson('/api/v1/users');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'username',
                            'email',
                            'status',
                        ],
                    ],
                    'current_page',
                    'per_page',
                    'total',
                    'message',
                ]);
        });

        it('allows admin to filter users by username', function () {
            User::factory()->create(['username' => 'Unique John Doe']);
            User::factory()->create(['username' => 'Jane Smith']);

            $response = $this->getJson('/api/v1/users?username=Unique John');

            $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.username', 'Unique John Doe')
                ->assertJsonStructure([
                    'data',
                    'current_page',
                    'per_page',
                    'total',
                    'message',
                ]);
        });

        it('allows admin to filter users by email', function () {
            $user1 = User::factory()->create(['email' => 'john@example.com']);
            $user2 = User::factory()->create(['email' => 'jane@example.com']);

            $response = $this->getJson('/api/v1/users?email=john@example.com');

            $response->assertStatus(200)
                ->assertJsonCount(1, 'data')
                ->assertJsonPath('data.0.email', 'john@example.com')
                ->assertJsonStructure([
                    'data',
                    'current_page',
                    'per_page',
                    'total',
                    'message',
                ]);
        });

        it('allows admin to filter users by status', function () {
            User::factory()->create(['status' => UserStatus::ACTIVE]);
            User::factory()->create(['status' => UserStatus::INACTIVE]);

            $response = $this->getJson('/api/v1/users?status='.UserStatus::ACTIVE->value);

            $response->assertStatus(200)
                ->assertJsonPath('total', 12) // 10 from UserSeeder + 1 admin + 1 from test
                ->assertJsonPath('data.0.status', UserStatus::ACTIVE->value)
                ->assertJsonStructure([
                    'data',
                    'current_page',
                    'per_page',
                    'total',
                    'message',
                ]);
        });

        it('supports pagination parameters', function () {
            User::factory()->count(15)->create();

            $response = $this->getJson('/api/v1/users?per_page=5&page=2');

            $response->assertStatus(200)
                ->assertJsonPath('current_page', 2)
                ->assertJsonPath('per_page', 5)
                ->assertJsonStructure([
                    'data',
                    'current_page',
                    'per_page',
                    'total',
                    'message',
                ]);
        });

        it('validates pagination parameters', function () {
            $response = $this->getJson('/api/v1/users?per_page=0');

            $response->assertStatus(422);

            $response = $this->getJson('/api/v1/users?per_page=101');

            $response->assertStatus(422);

            $response = $this->getJson('/api/v1/users?page=0');

            $response->assertStatus(422);
        });
    });

    describe('GET /api/v1/users/{id}', function () {
        it('allows admin to view any user profile', function () {
            $response = $this->getJson("/api/v1/users/{$this->user->id}");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'username',
                        'email',
                        'status',
                    ],
                    'message',
                ])
                ->assertJsonPath('data.id', $this->user->id);
        });

        it('allows admin to view their own profile', function () {
            $response = $this->getJson("/api/v1/users/{$this->admin->id}");

            $response->assertStatus(200)
                ->assertJsonPath('data.id', $this->admin->id);
        });

        it('returns 404 for non-existent user', function () {
            $response = $this->getJson('/api/v1/users/999');

            $response->assertStatus(404);
        });
    });

    describe('POST /api/v1/users', function () {
        it('allows admin to create new users', function () {
            $userData = [
                'username' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'MySecurePass123!@#',
                'password_confirmation' => 'MySecurePass123!@#',
            ];

            $response = $this->postJson('/api/v1/users', $userData);

            $response->assertStatus(201)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'username',
                        'email',
                        'status',
                    ],
                    'message',
                ])
                ->assertJsonPath('data.username', 'New User')
                ->assertJsonPath('data.email', 'newuser@example.com')
                ->assertJsonPath('data.status', UserStatus::ACTIVE->value);

            $this->assertDatabaseHas('users', [
                'username' => 'New User',
                'email' => 'newuser@example.com',
                'status' => UserStatus::ACTIVE->value,
            ]);
        });

        it('allows admin to create user with custom status', function () {
            $userData = [
                'username' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'MySecurePass123!@#',
                'password_confirmation' => 'MySecurePass123!@#',
                'status' => UserStatus::PENDING->value,
            ];

            $response = $this->postJson('/api/v1/users', $userData);

            $response->assertStatus(201)
                ->assertJsonPath('data.status', UserStatus::PENDING->value);

            $this->assertDatabaseHas('users', [
                'email' => 'newuser@example.com',
                'status' => UserStatus::PENDING->value,
            ]);
        });

        it('validates required fields', function () {
            $response = $this->postJson('/api/v1/users', []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['username', 'email']);
        });

        it('validates email format', function () {
            $response = $this->postJson('/api/v1/users', [
                'username' => 'Test User',
                'email' => 'invalid-email',
                'password' => 'password123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        it('validates unique email', function () {
            User::factory()->create(['email' => 'existing@example.com']);

            $response = $this->postJson('/api/v1/users', [
                'username' => 'Test User',
                'email' => 'existing@example.com',
                'password' => 'password123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
        });

        it('validates unique username', function () {
            User::factory()->create(['username' => 'existinguser']);

            $response = $this->postJson('/api/v1/users', [
                'username' => 'existinguser',
                'email' => 'test@example.com',
                'password' => 'password123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['username']);
        });

        it('validates password length', function () {
            $response = $this->postJson('/api/v1/users', [
                'username' => 'Test User',
                'email' => 'test@example.com',
                'password' => '123',
                'password_confirmation' => '123',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
        });

        it('validates password against HaveIBeenPwned data leaks', function () {
            $response = $this->postJson('/api/v1/users', [
                'username' => 'Test User',
                'email' => 'test@example.com',
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password'])
                ->assertJsonPath('errors.password.0', 'The given password has appeared in a data leak. Please choose a different password.');
        });
    });

    describe('PUT /api/v1/users/{id}', function () {
        it('allows admin to update any user', function () {
            $updateData = [
                'username' => 'Updated User',
                'email' => 'updated@example.com',
                'status' => UserStatus::INACTIVE->value,
            ];

            $response = $this->putJson("/api/v1/users/{$this->user->id}", $updateData);

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'username',
                        'email',
                        'status',
                    ],
                    'message',
                ])
                ->assertJsonPath('data.username', 'Updated User')
                ->assertJsonPath('data.email', 'updated@example.com')
                ->assertJsonPath('data.status', UserStatus::INACTIVE->value);

            $this->assertDatabaseHas('users', [
                'id' => $this->user->id,
                'username' => 'Updated User',
                'email' => 'updated@example.com',
                'status' => UserStatus::INACTIVE->value,
            ]);
        });

        it('allows admin to update user password', function () {
            $updateData = [
                'username' => $this->user->username,
                'email' => 'newemail@example.com',
                'password' => 'MySecurePass123!@#',
                'password_confirmation' => 'MySecurePass123!@#',
            ];

            $response = $this->putJson("/api/v1/users/{$this->user->id}", $updateData);

            $response->assertStatus(200);

            $this->assertDatabaseHas('users', [
                'id' => $this->user->id,
                'username' => $this->user->username,
                'email' => 'newemail@example.com',
            ]);

            // Check that password was hashed
            $updatedUser = User::find($this->user->id);
            expect(Hash::check('MySecurePass123!@#', $updatedUser->password))->toBeTrue();
        });

        it('returns 404 for non-existent user', function () {
            $response = $this->putJson('/api/v1/users/999', [
                'username' => 'Test',
                'email' => 'test@example.com',
            ]);

            $response->assertStatus(404);
        });

        it('validates required fields', function () {
            $response = $this->putJson("/api/v1/users/{$this->user->id}", []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['username', 'email']);
        });

        it('email uniqueness validation excludes current user', function () {
            $response = $this->putJson("/api/v1/users/{$this->user->id}", [
                'username' => fake()->unique()->userName(),
                'email' => $this->user->email,
            ]);

            $response->assertStatus(200);
        });

        it('username uniqueness validation excludes current user', function () {
            $response = $this->putJson("/api/v1/users/{$this->user->id}", [
                'username' => $this->user->username,
                'email' => fake()->unique()->safeEmail(),
            ]);

            $response->assertStatus(200);
        });

        it('validates password against HaveIBeenPwned when updating', function () {
            $response = $this->putJson("/api/v1/users/{$this->user->id}", [
                'username' => $this->user->username,
                'email' => $this->user->email,
                'password' => 'Password123!',
                'password_confirmation' => 'Password123!',
            ]);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['password'])
                ->assertJsonPath('errors.password.0', 'The given password has appeared in a data leak. Please choose a different password.');
        });
    });

    describe('DELETE /api/v1/users/{id}', function () {
        it('allows admin to delete any user', function () {
            $response = $this->deleteJson("/api/v1/users/{$this->user->id}");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'username',
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
            $response = $this->deleteJson('/api/v1/users/999');

            $response->assertStatus(404);
        });
    });
});

describe('User Controller - Normal Users', function () {
    beforeEach(function () {
        // Run the database seeder to create roles and admin user
        $this->seed(DatabaseSeeder::class);

        // Get admin user created by seeder
        $this->admin = User::where('username', 'admin')->first();

        // Create a normal user for testing
        $this->user = User::where('username', '!=', 'admin')->first();

        // Authenticate as normal user for all tests
        $this->actingAs($this->user, 'api');
    });

    describe('GET /api/v1/users', function () {
        it('denies normal users from viewing list of users', function () {
            $response = $this->getJson('/api/v1/users');

            $response->assertStatus(403);
        });
    });

    describe('GET /api/v1/users/{id}', function () {
        it('allows normal users to view their own profile', function () {
            $response = $this->getJson("/api/v1/users/{$this->user->id}");

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        'id',
                        'username',
                        'email',
                        'status',
                    ],
                    'message',
                ])
                ->assertJsonPath('data.id', $this->user->id);
        });

        it('denies normal users from viewing other users profiles', function () {
            $response = $this->getJson("/api/v1/users/{$this->admin->id}");

            $response->assertStatus(403);
        });

        it('returns 404 for non-existent user', function () {
            $response = $this->getJson('/api/v1/users/999');

            $response->assertStatus(404);
        });
    });

    describe('POST /api/v1/users', function () {
        it('denies normal users from creating new users', function () {
            $userData = [
                'username' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'MySecurePass123!@#',
                'password_confirmation' => 'MySecurePass123!@#',
            ];

            $response = $this->postJson('/api/v1/users', $userData);

            $response->assertStatus(403);
        });
    });

    describe('PUT /api/v1/users/{id}', function () {
        it('allows normal users to update their own profile', function () {
            $updateData = [
                'username' => 'Updated Normal User',
                'email' => 'updated@example.com',
            ];

            $response = $this->putJson("/api/v1/users/{$this->user->id}", $updateData);

            $response->assertStatus(200)
                ->assertJsonPath('data.username', 'Updated Normal User')
                ->assertJsonPath('data.email', 'updated@example.com');

            $this->assertDatabaseHas('users', [
                'id' => $this->user->id,
                'username' => 'Updated Normal User',
                'email' => 'updated@example.com',
            ]);
        });

        it('denies normal users from updating their own status', function () {
            $updateData = [
                'username' => $this->user->username,
                'email' => $this->user->email,
                'status' => UserStatus::INACTIVE->value,
            ];

            $response = $this->putJson("/api/v1/users/{$this->user->id}", $updateData);

            $response->assertStatus(403);
        });

        it('denies normal users from updating other users profiles', function () {
            $updateData = [
                'username' => 'Updated Admin',
                'email' => 'admin@example.com',
                'status' => UserStatus::INACTIVE->value,
            ];

            $response = $this->putJson("/api/v1/users/{$this->admin->id}", $updateData);

            $response->assertStatus(403);
        });

        it('validates required fields for normal users', function () {
            $response = $this->putJson("/api/v1/users/{$this->user->id}", []);

            $response->assertStatus(422)
                ->assertJsonValidationErrors(['username', 'email']);
        });
    });

    describe('DELETE /api/v1/users/{id}', function () {
        it('denies normal users from deleting users', function () {
            $response = $this->deleteJson("/api/v1/users/{$this->user->id}");

            $response->assertStatus(403);
        });

        it('returns 404 for non-existent user', function () {
            $response = $this->deleteJson('/api/v1/users/999');

            $response->assertStatus(404);
        });
    });
});
