<?php

declare(strict_types=1);

namespace App\Commands;

use App\Foundation\BaseData;
use App\Validation\Password;
use App\Validation\Username;
use App\Transformers\PasswordTransformer;
use Spatie\LaravelData\Attributes\WithTransformer;

class CreateUserCommand extends BaseData
{
    public function __construct(
        public string $username,
        public string $email,
        #[WithTransformer(PasswordTransformer::class)]
        public string $password,
    ) {}

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'username' => ['required', ...Username::strict(), 'unique:users,username'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::strong()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'username.regex' => Username::message(),
        ];
    }
}
