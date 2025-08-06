<?php

declare(strict_types=1);

namespace App\Commands;

use App\Enums\UserStatus;
use App\Foundation\BaseData;
use Illuminate\Validation\Rules\Enum;

final class CreateUserCommand extends BaseData
{
    /**
     * Create a new User instance.
     *
     * @param  string  $name  The user's full name
     * @param  string  $email  The user's email address
     * @param  string  $password  The user's password
     * @param  UserStatus  $status  The user's status
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public UserStatus $status = UserStatus::ACTIVE,
    ) {}

    /**
     * Get the validation rules for the command.
     *
     * @return array<string, string|array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'status'   => ['sometimes', 'required', new Enum(UserStatus::class)],
        ];
    }
}
