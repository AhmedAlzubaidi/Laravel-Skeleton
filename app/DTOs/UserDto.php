<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\UserStatus;
use App\Foundation\BaseData;

final class UserDto extends BaseData
{
    /**
     * @param  int  $id  The user's unique identifier
     * @param  string  $name  The user's full name
     * @param  string  $email  The user's email address
     * @param  string|null  $password  The user's password (nullable for security)
     * @param  UserStatus  $status  The user's current status
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public ?string $password,
        public UserStatus $status,
    ) {}
}
