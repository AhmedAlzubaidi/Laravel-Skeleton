<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\UserStatus;
use App\Foundation\BaseData;
use Illuminate\Validation\Rules\Enum;

class GetUsersQuery extends BaseData
{
    /**
     * Create a new GetUsersQuery instance.
     *
     * @param  ?string  $username  The username of the user to search for
     * @param  ?string  $email  The email of the user to search for
     * @param  ?UserStatus  $status  The status of the user to search for
     * @param  ?int  $per_page  Number of items per page (default: 10)
     * @param  ?int  $page  Page number (default: 1)
     */
    public function __construct(
        public ?string $username,
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
            'username' => ['sometimes', 'required', 'string', 'max:40'],
            'email'    => ['sometimes', 'required', 'email', 'exists:users,email'],
            'status'   => ['sometimes', 'required', new Enum(UserStatus::class)],
            'per_page' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'page'     => ['sometimes', 'required', 'integer', 'min:1'],
        ];
    }
}
