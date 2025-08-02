<?php

declare(strict_types=1);

namespace App\Commands;

use App\Enums\UserStatus;
use Illuminate\Validation\Rules\Enum;
use Spatie\LaravelData\Data;

class UpdateUserCommand extends Data
{
    /**
     * Create a new User instance.
     *
     * @param  string  $name  The user's full name
     * @param  string  $email  The user's email address
     * @param  string|null  $password  The user's password (nullable for security)
     * @param  UserStatus  $status  The user's status
     */
    public function __construct(
        public string $name,
        public string $email,
        public ?string $password = null,
        public UserStatus $status = UserStatus::ACTIVE,
    ) {}

    /**
     * Get the validation rules for the command.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        $userId = request()->route('id');

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email,'.$userId],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'status' => ['required', new Enum(UserStatus::class)],
        ];
    }
}
