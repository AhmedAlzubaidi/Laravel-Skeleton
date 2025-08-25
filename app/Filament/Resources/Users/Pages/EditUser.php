<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\EditRecord;

final class EditUser extends EditRecord
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
            DeleteAction::make(),
        ];
    }
}
