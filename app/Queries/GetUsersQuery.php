<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\UserStatus;
use Illuminate\Validation\Rules\Enum;
use Spatie\LaravelData\Data;

final class GetUsersQuery extends Data
{
    /**
     * @param  string|null  $name  The name of the user to search for
     * @param  string|null  $email  The email of the user to search for
     * @param  UserStatus|null  $status  The status of the user to search for
     */
    public function __construct(
        public ?string $name,
        public ?string $email,
        public ?UserStatus $status,
    ) {}

    /**
     * Get the validation rules for the query.
     *
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'exists:users,email'],
            'status' => ['sometimes', 'required', new Enum(UserStatus::class)],
        ];
    }
}
