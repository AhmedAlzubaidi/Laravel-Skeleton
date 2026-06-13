<?php

declare(strict_types=1);

namespace App\Commands;

use App\Foundation\BaseData;
use App\Validation\Password;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;
use App\Transformers\PasswordTransformer;
use Spatie\LaravelData\Attributes\WithTransformer;

class UpdateUserCommand extends BaseData
{
    public function __construct(
        public string $username,
        public string $email,
        #[WithTransformer(PasswordTransformer::class)]
        public ?string $password = null,
    ) {}

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        $userId = Route::current()?->parameter('user');

        return [
            'username' => ['required', 'string', 'max:40', Rule::unique('users', 'username')->ignore($userId)],
            'email'    => ['required', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'password' => ['sometimes', 'required', 'confirmed', Password::strong()],
        ];
    }
}
