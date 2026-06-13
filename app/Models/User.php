<?php

declare(strict_types=1);

namespace App\Models;

use Filament\Panel;
use Laravel\Passport\HasApiTokens;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\HasName;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Laravel\Passport\Contracts\OAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @mixin IdeHelperUser
 */
final class User extends Authenticatable implements FilamentUser, HasName, OAuthenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function findForPassport(string $username): ?self
    {
        return $this->where('username', $username)->orWhere('email', $username)->first();
    }

    public function getFilamentName(): string
    {
        return "{$this->username}";
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }
}
