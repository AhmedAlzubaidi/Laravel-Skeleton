<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case PENDING = 'pending';

    /**
     * Get all the values of the enum cases.
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_map(fn (UserStatus $status) => $status->value, self::cases());
    }

    /**
     * Get the label of the enum case.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::SUSPENDED => 'Suspended',
            self::PENDING => 'Pending',
        };
    }

    /**
     * Get the color of the enum case.
     */
    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'green',
            self::INACTIVE => 'gray',
            self::SUSPENDED => 'red',
            self::PENDING => 'yellow',
        };
    }
}
