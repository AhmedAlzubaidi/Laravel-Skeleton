<?php

declare(strict_types=1);

namespace App\DTOs;

use App\Foundation\BaseData;
use Spatie\LaravelData\Attributes\Hidden;

class UserDto extends BaseData
{
    public function __construct(
        public int $id,
        public string $username,
        public string $email,
        #[Hidden]
        public ?string $password,
    ) {}
}
