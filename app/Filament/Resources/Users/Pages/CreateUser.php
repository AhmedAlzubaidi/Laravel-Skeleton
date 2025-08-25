<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\Users\UserResource;

class CreateUser extends CreateRecord
{
    /**
     * @var class-string<UserResource>
     */
    protected static string $resource = UserResource::class;
}
