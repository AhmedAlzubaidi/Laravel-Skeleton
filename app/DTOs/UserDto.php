<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Enums\UserStatus;
use App\Foundation\BaseData;
use Spatie\LaravelData\Attributes\Hidden;

class UserDto extends BaseData
{
    /**
     * Create a new UserDto instance.
     */
    public function __construct(
        public int $id,
        public string $username,
        public string $email,
        #[Hidden]
        public ?string $password,
        public UserStatus $status,
    ) {}
}
