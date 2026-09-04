<?php

declare(strict_types=1);

use App\Models\User;
use Livewire\Livewire;
use App\Validation\Username;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Role;
use Database\Seeders\DatabaseSeeder;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\CreateUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('UserResource', function () {
    beforeEach(function () {
        $this->seed(DatabaseSeeder::class);

        Filament::setCurrentPanel('admin');
    });

    describe('authorization', function () {
        it('lets an admin open the list page', function () {
            $admin = User::factory()->create();
            $admin->assignRole('admin');

            Livewire::actingAs($admin)
                ->test(ListUsers::class)
                ->assertOk();
        });

        it('forbids a non-admin, since UserPolicy::viewAny denies everyone and only Gate::before lets admins through', function () {
            $user = User::factory()->create();
            $user->assignRole('user');

            Livewire::actingAs($user)
                ->test(ListUsers::class)
                ->assertForbidden();
        });
    });

    describe('list page', function () {
        beforeEach(function () {
            $this->admin = User::factory()->create();
            $this->admin->assignRole('admin');
        });

        it('shows existing users in the table', function () {
            $users = User::all();

            Livewire::actingAs($this->admin)
                ->test(ListUsers::class)
                ->assertCanSeeTableRecords($users->take(10));
        });

        it('renders the username and email columns', function () {
            $user = User::factory()->create(['username' => 'listed_user']);

            Livewire::actingAs($this->admin)
                ->test(ListUsers::class)
                ->assertCanRenderTableColumn('username')
                ->assertCanRenderTableColumn('email')
                ->assertTableColumnStateSet('username', 'listed_user', $user);
        });
    });

    describe('create page', function () {
        beforeEach(function () {
            $this->admin = User::factory()->create();
            $this->admin->assignRole('admin');
        });

        it('creates a user', function () {
            Livewire::actingAs($this->admin)
                ->test(CreateUser::class)
                ->fillForm([
                    'username'              => 'created_user',
                    'email'                 => 'created@example.com',
                    'password'              => 'MySecurePass123!@#',
                    'password_confirmation' => 'MySecurePass123!@#',
                    'roles'                 => [Role::where('name', 'user')->value('id')],
                ])
                ->call('create')
                ->assertHasNoFormErrors();

            $created = User::where('username', 'created_user')->sole();

            expect($created->hasRole('user'))->toBeTrue()
                ->and($created->password)->not->toBe('MySecurePass123!@#');
        });

        it('rejects a username outside the allowed character set', function () {
            Livewire::actingAs($this->admin)
                ->test(CreateUser::class)
                ->fillForm([
                    'username'              => 'invalid user',
                    'email'                 => 'invalid@example.com',
                    'password'              => 'MySecurePass123!@#',
                    'password_confirmation' => 'MySecurePass123!@#',
                    'roles'                 => [Role::where('name', 'user')->value('id')],
                ])
                ->call('create')
                ->assertHasFormErrors(['username']);

            expect(User::where('email', 'invalid@example.com')->exists())->toBeFalse();
        });

        it('rejects a username longer than the shared maximum', function () {
            Livewire::actingAs($this->admin)
                ->test(CreateUser::class)
                ->fillForm([
                    'username' => str_repeat('a', Username::MAX_LENGTH + 1),
                    'email'    => 'toolong@example.com',
                ])
                ->call('create')
                ->assertHasFormErrors(['username']);
        });
    });

    describe('edit page', function () {
        beforeEach(function () {
            $this->admin = User::factory()->create();
            $this->admin->assignRole('admin');
        });

        it('loads the record into the form', function () {
            $user = User::factory()->create(['username' => 'before_edit']);

            Livewire::actingAs($this->admin)
                ->test(EditUser::class, ['record' => $user->getKey()])
                ->assertOk()
                ->assertFormSet([
                    'username' => 'before_edit',
                    'email'    => $user->email,
                ]);
        });

        it('saves a changed username without requiring a password', function () {
            $user             = User::factory()->create(['username' => 'before_edit']);
            $user->assignRole('user');
            $originalPassword = $user->password;

            Livewire::actingAs($this->admin)
                ->test(EditUser::class, ['record' => $user->getKey()])
                ->fillForm(['username' => 'after_edit'])
                ->call('save')
                ->assertHasNoFormErrors();

            expect($user->refresh()->username)->toBe('after_edit')
                ->and($user->password)->toBe($originalPassword);
        });

        it('rejects an invalid username on update', function () {
            $user = User::factory()->create(['username' => 'before_edit']);
            $user->assignRole('user');

            Livewire::actingAs($this->admin)
                ->test(EditUser::class, ['record' => $user->getKey()])
                ->fillForm(['username' => 'after edit'])
                ->call('save')
                ->assertHasFormErrors(['username']);

            expect($user->refresh()->username)->toBe('before_edit');
        });
    });
});
