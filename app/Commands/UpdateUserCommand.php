<?php

declare(strict_types=1);

namespace App\Commands;

use App\Enums\UserStatus;
use App\Foundation\BaseData;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Enum;
use App\Transformers\PasswordTransformer;
use Illuminate\Validation\Rules\Password;
use Spatie\LaravelData\Attributes\WithTransformer;

class UpdateUserCommand extends BaseData
{
    /**
     * Create a new UpdateUserCommand instance.
     */
    public function __construct(
        public string $username,
        public string $email,
        #[WithTransformer(PasswordTransformer::class)]
        public ?string $password = null,
        public ?UserStatus $status = null,
    ) {}

    /**
     * Get the validation rules for the command.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        /** @var string $userId */
        $userId = Route::input('user');

        return [
            'username' => ['required', 'string', 'max:40', 'unique:users,username,'.$userId],
            'email'    => ['required', 'email', 'unique:users,email,'.$userId],
            'password' => [
                'sometimes',
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
