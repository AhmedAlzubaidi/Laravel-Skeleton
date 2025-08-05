<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\UserStatus;
use Spatie\LaravelData\Data;
use Illuminate\Validation\Rules\Enum;

final class GetUsersQuery extends Data
{
    /**
     * @param  string|null  $name  The name of the user to search for
     * @param  string|null  $email  The email of the user to search for
     * @param  UserStatus|null  $status  The status of the user to search for
     * @param  int|null  $per_page  Number of items per page (default: 10)
     * @param  int|null  $page  Page number (default: 1)
     */
    public function __construct(
        public ?string $name,
        public ?string $email,
        public ?UserStatus $status,
        public ?int $per_page = 10,
        public ?int $page = 1,
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
            'per_page' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'required', 'integer', 'min:1'],
        ];
    }
}
