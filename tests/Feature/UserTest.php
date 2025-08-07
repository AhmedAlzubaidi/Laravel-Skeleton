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

        it('handles empty string input', function () {
            $foundUser = (new User())->findForPassport('');

            expect($foundUser)->toBeNull();
        });

        it('handles whitespace-only input', function () {
            $foundUser = (new User())->findForPassport('   ');

            expect($foundUser)->toBeNull();
        });

        it('handles special characters in username', function () {
            $user = User::factory()->create(['username' => 'test_user-123']);

            $foundUser = (new User())->findForPassport('test_user-123');

            expect($foundUser)->toBeInstanceOf(User::class)
                ->and($foundUser->id)->toBe($user->id);
        });

        it('handles special characters in email', function () {
            $user = User::factory()->create(['email' => 'test+tag@example.com']);

            $foundUser = (new User())->findForPassport('test+tag@example.com');

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

        it('works with multiple panels', function () {
            $admin = User::where('username', 'admin')->first();
            $panel1 = new Panel('admin');
            $panel2 = new Panel('dashboard');
            $panel3 = new Panel('settings');

            expect($admin->canAccessPanel($panel1))->toBeTrue();
            expect($admin->canAccessPanel($panel2))->toBeTrue();
            expect($admin->canAccessPanel($panel3))->toBeTrue();
        });

        it('consistently denies access for non-admin users across panels', function () {
            $normalUser = User::factory()->create();
            $normalUser->assignRole('user');
            $panel1 = new Panel('admin');
            $panel2 = new Panel('dashboard');
            $panel3 = new Panel('settings');

            expect($normalUser->canAccessPanel($panel1))->toBeFalse();
            expect($normalUser->canAccessPanel($panel2))->toBeFalse();
            expect($normalUser->canAccessPanel($panel3))->toBeFalse();
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

        it('returns false for users with multiple roles but not admin', function () {
            $userWithMultipleRoles = User::factory()->create();
            $userWithMultipleRoles->assignRole('user');

            $isAdmin = $userWithMultipleRoles->isAdmin();

            expect($isAdmin)->toBeFalse();
        });

        it('returns true for users with admin role among multiple roles', function () {
            $userWithMultipleRoles = User::factory()->create();
            $userWithMultipleRoles->assignRole('user');
            $userWithMultipleRoles->assignRole('admin');

            $isAdmin = $userWithMultipleRoles->isAdmin();

            expect($isAdmin)->toBeTrue();
        });
    });

    describe('getFilamentName', function () {
        it('returns username as filament name', function () {
            $user = User::factory()->create(['username' => 'testuser']);

            $filamentName = $user->getFilamentName();

            expect($filamentName)->toBe('testuser');
        });

        it('returns username with special characters', function () {
            $user = User::factory()->create(['username' => 'test_user-123']);

            $filamentName = $user->getFilamentName();

            expect($filamentName)->toBe('test_user-123');
        });

        it('returns username with numbers', function () {
            $user = User::factory()->create(['username' => 'user123']);

            $filamentName = $user->getFilamentName();

            expect($filamentName)->toBe('user123');
        });

        it('returns username with mixed case', function () {
            $user = User::factory()->create(['username' => 'TestUser']);

            $filamentName = $user->getFilamentName();

            expect($filamentName)->toBe('TestUser');
        });

        it('returns admin username correctly', function () {
            $admin = User::where('username', 'admin')->first();

            $filamentName = $admin->getFilamentName();

            expect($filamentName)->toBe('admin');
        });
    });

    describe('Model Attributes and Casting', function () {
        it('has correct fillable attributes', function () {
            $user = new User();

            expect($user->getFillable())->toBe([
                'username',
                'email',
                'password',
                'status',
            ]);
        });

        it('has correct hidden attributes', function () {
            $user = new User();

            expect($user->getHidden())->toBe([
                'password',
                'remember_token',
            ]);
        });

        it('casts status to UserStatus enum', function () {
            $user = User::factory()->create(['status' => 'active']);

            expect($user->status)->toBeInstanceOf(App\Enums\UserStatus::class);
        });

        it('casts email_verified_at to datetime', function () {
            $user = User::factory()->create(['email_verified_at' => '2023-01-01 12:00:00']);

            expect($user->email_verified_at)->toBeInstanceOf(Carbon\Carbon::class);
        });

        it('hashes password automatically', function () {
            $user = User::factory()->create(['password' => 'plaintext']);

            expect($user->password)->not->toBe('plaintext');
            expect(Illuminate\Support\Facades\Hash::check('plaintext', $user->password))->toBeTrue();
        });
    });

    describe('Traits and Interfaces', function () {
        it('implements FilamentUser interface', function () {
            $user = new User();

            expect($user)->toBeInstanceOf(Filament\Models\Contracts\FilamentUser::class);
        });

        it('implements HasName interface', function () {
            $user = new User();

            expect($user)->toBeInstanceOf(Filament\Models\Contracts\HasName::class);
        });

        it('implements OAuthenticatable interface', function () {
            $user = new User();

            expect($user)->toBeInstanceOf(Laravel\Passport\Contracts\OAuthenticatable::class);
        });

        it('uses HasApiTokens trait', function () {
            $user = new User();

            expect(method_exists($user, 'tokens'))->toBeTrue();
        });

        it('uses HasRoles trait', function () {
            $user = new User();

            expect(method_exists($user, 'hasRole'))->toBeTrue();
        });

        it('uses HasFactory trait', function () {
            $user = new User();

            expect(method_exists($user, 'factory'))->toBeTrue();
        });

        it('uses Notifiable trait', function () {
            $user = new User();

            expect(method_exists($user, 'notify'))->toBeTrue();
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

        it('complete user workflow', function () {
            // Create a user
            $user = User::factory()->create([
                'username' => 'testuser',
                'email' => 'test@example.com',
            ]);
            $user->assignRole('user');

            // Test findForPassport
            $foundUser = (new User())->findForPassport('testuser');
            expect($foundUser)->toBeInstanceOf(User::class)
                ->and($foundUser->id)->toBe($user->id);

            // Test findForPassport with email
            $foundUserByEmail = (new User())->findForPassport('test@example.com');
            expect($foundUserByEmail)->toBeInstanceOf(User::class)
                ->and($foundUserByEmail->id)->toBe($user->id);

            // Test isAdmin
            expect($user->isAdmin())->toBeFalse();

            // Test canAccessPanel
            $panel = new Panel('admin');
            expect($user->canAccessPanel($panel))->toBeFalse();

            // Test getFilamentName
            expect($user->getFilamentName())->toBe('testuser');

            // Promote to admin
            $user->assignRole('admin');

            // Test isAdmin after promotion
            expect($user->isAdmin())->toBeTrue();

            // Test canAccessPanel after promotion
            expect($user->canAccessPanel($panel))->toBeTrue();
        });

        it('admin user has all required capabilities', function () {
            $admin = User::where('username', 'admin')->first();

            // Test all admin capabilities
            expect($admin->isAdmin())->toBeTrue();
            expect($admin->canAccessPanel(new Panel('admin')))->toBeTrue();
            expect($admin->canAccessPanel(new Panel('dashboard')))->toBeTrue();
            expect($admin->getFilamentName())->toBe('admin');

            // Test findForPassport works
            $foundUser = (new User())->findForPassport('admin');
            expect($foundUser)->toBeInstanceOf(User::class)
                ->and($foundUser->id)->toBe($admin->id);

            $foundUserByEmail = (new User())->findForPassport('admin@admin.com');
            expect($foundUserByEmail)->toBeInstanceOf(User::class)
                ->and($foundUserByEmail->id)->toBe($admin->id);
        });
    });

    describe('Edge Cases and Error Handling', function () {
        it('handles empty username gracefully', function () {
            $user = User::factory()->create(['username' => '']);

            $filamentName = $user->getFilamentName();

            expect($filamentName)->toBe('');
        });

        it('handles very long usernames', function () {
            $longUsername = str_repeat('a', 40);
            $user = User::factory()->create(['username' => $longUsername]);

            $filamentName = $user->getFilamentName();

            expect($filamentName)->toBe($longUsername);
        });

        it('handles usernames with unicode characters', function () {
            $user = User::factory()->create(['username' => 'tëstüser']);

            $filamentName = $user->getFilamentName();

            expect($filamentName)->toBe('tëstüser');
        });

        it('handles email with unicode characters', function () {
            $user = User::factory()->create(['email' => 'tëst@exämple.com']);

            $foundUser = (new User())->findForPassport('tëst@exämple.com');

            expect($foundUser)->toBeInstanceOf(User::class)
                ->and($foundUser->id)->toBe($user->id);
        });
    });
});
