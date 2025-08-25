<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use Filament\Actions\CreateAction;
use Filament\Actions;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\ListRecords;

final class ListUsers extends ListRecords
{
    /**
     * @var class-string<UserResource>
     */
    protected static string $resource = UserResource::class;

    /**
     * @return array<Actions\Action>
     */
    public function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
