<?php

declare(strict_types=1);

use Filament\Panel;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('User Model', function () {
    beforeEach(function () {
        $this->seed(DatabaseSeeder::class);
    });

    describe('findForPassport', function () {
        it('finds user by username', function () {
            $user = User::factory()->create(['username' => 'testuser']);

            $foundUser = (new User())->findForPassport('testuser');

            expect($foundUser)->toBeInstanceOf(User::class)
                ->and($foundUser->id)->toBe($user->id)
                ->and($foundUser->username)->toBe('testuser');
        });

        it('finds user by email', function () {
            $user = User::factory()->create(['email' => 'test@example.com']);

            $foundUser = (new User())->findForPassport('test@example.com');

            expect($foundUser)->toBeInstanceOf(User::class)
                ->and($foundUser->id)->toBe($user->id)
                ->and($foundUser->email)->toBe('test@example.com');
        });

        it('finds user by username when both username and email match', function () {
            $user = User::factory()->create([
                'username' => 'testuser',
                'email' => 'testuser@example.com',
            ]);

            $foundUser = (new User())->findForPassport('testuser');

            expect($foundUser)->toBeInstanceOf(User::class)
                ->and($foundUser->id)->toBe($user->id)
                ->and($foundUser->username)->toBe('testuser');
        });

        it('finds user by email when both username and email match', function () {
            $user = User::factory()->create([
                'username' => 'testuser',
                'email' => 'testuser@example.com',
            ]);

            $foundUser = (new User())->findForPassport('testuser@example.com');

            expect($foundUser)->toBeInstanceOf(User::class)
                ->and($foundUser->id)->toBe($user->id)
                ->and($foundUser->email)->toBe('testuser@example.com');
        });

        it('returns null when user not found by username', function () {
            $foundUser = (new User())->findForPassport('nonexistentuser');

            expect($foundUser)->toBeNull();
        });

        it('returns null when user not found by email', function () {
            $foundUser = (new User())->findForPassport('nonexistent@example.com');

            expect($foundUser)->toBeNull();
        });

        it('handles case sensitive search', function () {
            $user = User::factory()->create(['username' => 'TestUser']);

            $foundUser = (new User())->findForPassport('TestUser');

            expect($foundUser)->toBeInstanceOf(User::class)
                ->and($foundUser->id)->toBe($user->id);
        });

        it('handles email case sensitivity', function () {
            $user = User::factory()->create(['email' => 'Test@Example.com']);

            $foundUser = (new User())->findForPassport('Test@Example.com');

            expect($foundUser)->toBeInstanceOf(User::class)
                ->and($foundUser->id)->toBe($user->id);
        });
    });

    describe('canAccessPanel', function () {
        it('allows admin users to access panel', function () {
            $admin = User::where('username', 'admin')->first();
            $panel = new Panel('admin');

            $canAccess = $admin->canAccessPanel($panel);

            expect($canAccess)->toBeTrue();
        });

        it('denies normal users from accessing panel', function () {
            $normalUser = User::factory()->create();
            $normalUser->assignRole('user');
            $panel = new Panel('admin');

            $canAccess = $normalUser->canAccessPanel($panel);

            expect($canAccess)->toBeFalse();
        });

        it('denies users without roles from accessing panel', function () {
            $userWithoutRole = User::factory()->create();
            $panel = new Panel('admin');

            $canAccess = $userWithoutRole->canAccessPanel($panel);

            expect($canAccess)->toBeFalse();
        });

        it('works with different panel names', function () {
            $admin = User::where('username', 'admin')->first();
            $customPanel = new Panel('custom');

            $canAccess = $admin->canAccessPanel($customPanel);

            expect($canAccess)->toBeTrue();
        });
    });

    describe('isAdmin', function () {
        it('returns true for admin users', function () {
            $admin = User::where('username', 'admin')->first();

            $isAdmin = $admin->isAdmin();

            expect($isAdmin)->toBeTrue();
        });

        it('returns false for normal users', function () {
            $normalUser = User::factory()->create();
            $normalUser->assignRole('user');

            $isAdmin = $normalUser->isAdmin();

            expect($isAdmin)->toBeFalse();
        });

        it('returns false for users without roles', function () {
            $userWithoutRole = User::factory()->create();

            $isAdmin = $userWithoutRole->isAdmin();

            expect($isAdmin)->toBeFalse();
        });

        it('returns false for users with other roles', function () {
            $userWithOtherRole = User::factory()->create();
            $userWithOtherRole->assignRole('user');

            $isAdmin = $userWithOtherRole->isAdmin();

            expect($isAdmin)->toBeFalse();
        });
    });

    describe('Integration', function () {
        it('findForPassport works with admin user from seeder', function () {
            $foundUser = (new User())->findForPassport('admin');

            expect($foundUser)->toBeInstanceOf(User::class)
                ->and($foundUser->username)->toBe('admin')
                ->and($foundUser->isAdmin())->toBeTrue();
        });

        it('admin user can access panel', function () {
            $admin = (new User())->findForPassport('admin');
            $panel = new Panel('admin');

            $canAccess = $admin->canAccessPanel($panel);

            expect($canAccess)->toBeTrue();
        });

        it('findForPassport with admin email', function () {
            $foundUser = (new User())->findForPassport('admin@admin.com');

            expect($foundUser)->toBeInstanceOf(User::class)
                ->and($foundUser->email)->toBe('admin@admin.com')
                ->and($foundUser->isAdmin())->toBeTrue();
        });
    });
});
