<?php

declare(strict_types=1);

use Database\Seeders\UserSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds roles without demo accounts outside local and test environments', function (string $environment): void {
    $this->app->detectEnvironment(fn (): string => $environment);

    $this->artisan('db:seed', ['--class' => DatabaseSeeder::class, '--force' => true])->assertSuccessful();

    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseHas('roles', ['name' => 'admin']);
    $this->assertDatabaseHas('roles', ['name' => 'user']);
})->with(['production', 'staging']);

it('does not create demo accounts when UserSeeder is called directly in production', function (): void {
    $this->app->detectEnvironment(fn (): string => 'production');

    $this->artisan('db:seed', ['--class' => UserSeeder::class, '--force' => true])->assertSuccessful();

    $this->assertDatabaseCount('users', 0);
});

it('keeps demo accounts available for development and tests', function (string $environment): void {
    $this->app->detectEnvironment(fn (): string => $environment);

    $this->seed(DatabaseSeeder::class);

    $this->assertDatabaseCount('users', 11);
    $this->assertDatabaseHas('users', ['username' => 'admin']);
})->with(['local', 'testing']);
