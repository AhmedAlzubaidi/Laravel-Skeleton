<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Factories\Factory;

arch()->preset()->php();
arch()->preset()->strict()
    ->ignoring([
        'App\Foundation',
        'App\Filament',
    ]);
arch()->preset()->laravel()->ignoring([
    'App\Providers\Filament\AdminPanelProvider',
]);
arch()->preset()->security();

arch('strict types')
    ->expect('App')
    ->toUseStrictTypes();

arch('no die, dd, dump')
    ->expect('App')
    ->not->toUse(['die', 'dd', 'dump']);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();

arch('requests')
    ->expect('App\Http\Requests')
    ->not->toBeUsed();

arch('resources')
    ->expect('App\Http\Resources')
    ->not->toBeUsed();

arch('avoid mutation')
    ->expect('App')
    ->classes()
    ->toBeReadonly()
    ->ignoring([
        'App\Exceptions',
        'App\Foundation',
        'App\Providers',
        'App\Services',
        'App\Commands',
        'App\Filament',
        'App\Queries',
        'App\Models',
        'App\DTOs',
        'App\Jobs',
    ]);

arch('annotations')
    ->expect('App')
    ->toHavePropertiesDocumented()
    ->toHaveMethodsDocumented();

arch('factories')
    ->expect('Database\Factories')
    ->toExtend(Factory::class)
    ->toHaveMethod('definition')
    ->toOnlyBeUsedIn([
        'App\Models',
    ]);

arch('models')
    ->expect('App\Models')
    ->toHaveMethod('casts')
    ->toOnlyBeUsedIn([
        'Database\Factories',
        'Database\Seeders',
        'App\Providers',
        'App\Filament',
        'App\Services',
        'App\Policies',
        'App\Models',
        'App\Jobs',
        'App\Http',
    ]);

// enable this when the project has a Traits folder
// arch('Traits folder contains only traits')
//     ->expect('App\*\Traits')
//     ->toBeTraits();
