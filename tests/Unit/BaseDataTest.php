<?php

declare(strict_types=1);

use App\DTOs\UserDto;
use App\Enums\UserStatus;
use App\Foundation\BaseData;
use App\Queries\GetUsersQuery;
use App\Commands\CreateUserCommand;
use App\Commands\UpdateUserCommand;
use Illuminate\Support\Facades\Route;

describe('BaseData Abstract Class', function () {
    describe('rules() method', function () {
        it('returns empty array for UserDto (default implementation)', function () {
            $rules = UserDto::rules();

            expect($rules)->toBeArray();
            expect($rules)->toBeEmpty();
        });

        it('returns validation rules for CreateUserCommand', function () {
            $rules = CreateUserCommand::rules();

            expect($rules)->toBeArray();
            expect($rules)->toHaveKey('username');
            expect($rules)->toHaveKey('email');
            expect($rules)->toHaveKey('password');
            expect($rules)->toHaveKey('status');

            expect($rules['username'])->toContain('required');
            expect($rules['email'])->toContain('required');
            expect($rules['password'])->toContain('required');
        });

        it('returns validation rules for UpdateUserCommand', function () {
            // Mock the route context for UpdateUserCommand
            Route::shouldReceive('input')
                ->with('user')
                ->andReturn('1');

            $rules = UpdateUserCommand::rules();

            expect($rules)->toBeArray();
            expect($rules)->toHaveKey('username');
            expect($rules)->toHaveKey('email');
            expect($rules)->toHaveKey('password');
            expect($rules)->toHaveKey('status');

            expect($rules['username'])->toContain('required');
            expect($rules['email'])->toContain('required');
            expect($rules['password'])->toContain('sometimes');
        });

        it('returns validation rules for GetUsersQuery', function () {
            $rules = GetUsersQuery::rules();

            expect($rules)->toBeArray();
            expect($rules)->toHaveKey('username');
            expect($rules)->toHaveKey('email');
            expect($rules)->toHaveKey('status');
            expect($rules)->toHaveKey('per_page');
            expect($rules)->toHaveKey('page');

            expect($rules['username'])->toContain('sometimes');
            expect($rules['email'])->toContain('sometimes');
            expect($rules['status'])->toContain('sometimes');
        });
    });

    describe('validated() method', function () {
        it('filters out optional fields when rules are defined', function () {
            // Create a command with optional fields
            $command   = new CreateUserCommand(
                username: 'testuser',
                email: 'test@example.com',
                password: 'password123',
                status: UserStatus::ACTIVE
            );

            $validated = $command->validated();

            expect($validated)->toBeArray();
            expect($validated)->toHaveKey('username');
            expect($validated)->toHaveKey('email');
            expect($validated)->toHaveKey('password');
            expect($validated)->toHaveKey('status');
        });
    });

    describe('toArray() method', function () {
        it('returns all data for UserDto (DTOs should use toArray)', function () {
            $userDto = new UserDto(
                id: 1,
                username: 'testuser',
                email: 'test@example.com',
                password: 'hashed_password',
                status: UserStatus::ACTIVE
            );

            $array   = $userDto->toArray();

            expect($array)->toBeArray();
            expect($array)->toHaveKey('id');
            expect($array)->toHaveKey('username');
            expect($array)->toHaveKey('email');
            expect($array)->not->toHaveKey('password'); // Password is hidden via #[Hidden] attribute
            expect($array)->toHaveKey('status');
        });

        it('returns all data for Commands (Commands can use toArray for full data)', function () {
            $command = new CreateUserCommand(
                username: 'testuser',
                email: 'test@example.com',
                password: 'password123',
                status: UserStatus::ACTIVE
            );

            $array   = $command->toArray();

            expect($array)->toBeArray();
            expect($array)->toHaveKey('username');
            expect($array)->toHaveKey('email');
            expect($array)->toHaveKey('password');
            expect($array)->toHaveKey('status');
        });
    });

    describe('Integration with concrete classes', function () {
        it('can be instantiated through CreateUserCommand', function () {
            $command = new CreateUserCommand(
                username: 'testuser',
                email: 'test@example.com',
                password: 'password123',
                status: UserStatus::ACTIVE
            );

            expect($command)->toBeInstanceOf(CreateUserCommand::class);
            expect($command)->toBeInstanceOf(BaseData::class);
        });

        it('can be instantiated through GetUsersQuery', function () {
            $query = new GetUsersQuery(
                username: 'testuser',
                email: 'test@example.com',
                status: UserStatus::ACTIVE,
                perPage: 10,
                page: 1
            );

            expect($query)->toBeInstanceOf(GetUsersQuery::class);
            expect($query)->toBeInstanceOf(BaseData::class);
        });
    });
});
