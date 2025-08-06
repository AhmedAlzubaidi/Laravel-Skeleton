<?php

declare(strict_types=1);

namespace App\Commands;

use App\Enums\UserStatus;
use App\Foundation\BaseData;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

final class CreateUserCommand extends BaseData
{
    /**
     * Create a new User instance.
     *
     * @param  string  $username  The user's username
     * @param  string  $email  The user's email address
     * @param  string  $password  The user's password
     * @param  UserStatus  $status  The user's status
     */
    public function __construct(
        public string $username,
        public string $email,
        public string $password,
        public UserStatus $status = UserStatus::ACTIVE,
    ) {}

    /**
     * Get the validation rules for the command.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:40', 'unique:users,username'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->uncompromised(), // Checks against data leaks via HaveIBeenPwned
            ],
            'status'   => ['sometimes', 'required', new Enum(UserStatus::class)],
        ];
    }
}
