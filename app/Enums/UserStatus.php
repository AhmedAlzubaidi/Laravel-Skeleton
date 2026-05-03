<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE    = 'active';
    case INACTIVE  = 'inactive';
    case SUSPENDED = 'suspended';
    case PENDING   = 'pending';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(fn (UserStatus $status) => $status->value, self::cases());
    }

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE    => 'Active',
            self::INACTIVE  => 'Inactive',
            self::SUSPENDED => 'Suspended',
            self::PENDING   => 'Pending',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE    => 'green',
            self::INACTIVE  => 'gray',
            self::SUSPENDED => 'red',
            self::PENDING   => 'yellow',
        };
    }
}
