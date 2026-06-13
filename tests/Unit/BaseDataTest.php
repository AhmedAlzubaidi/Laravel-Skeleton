<?php

declare(strict_types=1);

use App\DTOs\UserDto;
use App\Foundation\BaseData;
use App\Queries\GetUsersQuery;
use App\Commands\CreateUserCommand;
use App\Commands\UpdateUserCommand;

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

            expect($rules['username'])->toContain('required');
            expect($rules['email'])->toContain('required');
            expect($rules['password'])->toContain('required');
        });

        it('returns validation rules for UpdateUserCommand', function () {
            $rules = UpdateUserCommand::rules();

            expect($rules)->toBeArray();
            expect($rules)->toHaveKey('username');
            expect($rules)->toHaveKey('email');
            expect($rules)->toHaveKey('password');

            expect($rules['username'])->toContain('required');
            expect($rules['email'])->toContain('required');
            expect($rules['password'])->toContain('sometimes');
        });

        it('returns validation rules for GetUsersQuery', function () {
            $rules = GetUsersQuery::rules();

            expect($rules)->toBeArray();
            expect($rules)->toHaveKey('username');
            expect($rules)->toHaveKey('email');
            expect($rules)->toHaveKey('per_page');
            expect($rules)->toHaveKey('page');

            expect($rules['username'])->toContain('sometimes');
            expect($rules['email'])->toContain('sometimes');
        });
    });

    describe('validated() method', function () {
        it('filters out optional fields when they are unset', function () {
            // password is a 'sometimes' rule on UpdateUserCommand; left unset it
            // should be stripped from the validated payload.
            $command   = new UpdateUserCommand(
                username: 'testuser',
                email: 'test@example.com',
            );

            $validated = $command->validated();

            expect($validated)->toBeArray();
            expect($validated)->toHaveKey('username');
            expect($validated)->toHaveKey('email');
            expect($validated)->not->toHaveKey('password');
        });
    });

    describe('toArray() method', function () {
        it('returns all data for UserDto (DTOs should use toArray)', function () {
            $userDto = new UserDto(
                id: 1,
                username: 'testuser',
                email: 'test@example.com',
                password: 'hashed_password',
            );

            $array   = $userDto->toArray();

            expect($array)->toBeArray();
            expect($array)->toHaveKey('id');
            expect($array)->toHaveKey('username');
            expect($array)->toHaveKey('email');
            expect($array)->not->toHaveKey('password'); // Password is hidden via #[Hidden] attribute
        });

        it('returns all data for Commands (Commands can use toArray for full data)', function () {
            $command = new CreateUserCommand(
                username: 'testuser',
                email: 'test@example.com',
                password: 'password123',
            );

            $array   = $command->toArray();

            expect($array)->toBeArray();
            expect($array)->toHaveKey('username');
            expect($array)->toHaveKey('email');
            expect($array)->toHaveKey('password');
        });
    });

    describe('Integration with concrete classes', function () {
        it('can be instantiated through CreateUserCommand', function () {
            $command = new CreateUserCommand(
                username: 'testuser',
                email: 'test@example.com',
                password: 'password123',
            );

            expect($command)->toBeInstanceOf(CreateUserCommand::class);
            expect($command)->toBeInstanceOf(BaseData::class);
        });

        it('can be instantiated through GetUsersQuery', function () {
            $query = new GetUsersQuery(
                username: 'testuser',
                email: 'test@example.com',
                perPage: 10,
                page: 1
            );

            expect($query)->toBeInstanceOf(GetUsersQuery::class);
            expect($query)->toBeInstanceOf(BaseData::class);
        });
    });
});
