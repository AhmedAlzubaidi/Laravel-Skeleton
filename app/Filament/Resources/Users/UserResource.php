<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use BackedEnum;
use App\Models\User;
use Filament\Pages\Page;
use App\Enums\UserStatus;
use Filament\Tables\Table;
use Filament\Schemas\Schema;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Spatie\Permission\Models\Role;
use Filament\Actions\BulkActionGroup;
use Filament\Forms\Components\Select;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Validation\Rules\Password;
use Filament\Resources\Pages\PageRegistration;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\CreateUser;

class UserResource extends Resource
{
    /**
     * @var class-string<User>
     */
    protected static ?string $model = User::class;

    /**
     * The navigation icon for the resource.
     */
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Builds the form for the resource.
     */
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('username')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('password')
                    ->password()
                    ->rules([
                        'confirmed',
                        Password::min(8)
                            ->mixedCase()
                            ->numbers()
                            ->symbols()
                            ->uncompromised(),
                    ])
                    ->required(fn (Page $livewire): bool => ($livewire instanceof CreateUser)),
                TextInput::make('password_confirmation')
                    ->password()
                    ->required(fn (Page $livewire): bool => ($livewire instanceof CreateUser)),
                Select::make('status')
                    ->options(UserStatus::class)
                    ->default(UserStatus::ACTIVE)
                    ->required(false),
                Select::make('roles')
                    ->label('Role')
                    ->relationship('roles', 'name')
                    ->required(fn (Page $livewire): bool => ($livewire instanceof CreateUser)),
            ]);
    }

    /**
     * Builds the table for the resource.
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('username'),
                TextColumn::make('email'),
                TextColumn::make('status'),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->default('No Role'),
                TextColumn::make('created_at')
                    ->dateTime(),
                TextColumn::make('updated_at')
                    ->dateTime(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(UserStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * @return array<class-string>
     */
    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index'  => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit'   => EditUser::route('/{record}/edit'),
        ];
    }
}
