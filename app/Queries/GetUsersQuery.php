<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\UserStatus;
use App\Foundation\BaseData;
use Illuminate\Validation\Rules\Enum;
use Spatie\LaravelData\Attributes\MapInputName;

class GetUsersQuery extends BaseData
{
    /**
     * Create a new GetUsersQuery instance.
     */
    public function __construct(
        public ?string $username,
        public ?string $email,
        public ?UserStatus $status,
        #[MapInputName('per_page')]
        public int $perPage = 10,
        public int $page = 1,
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
            'email'    => ['sometimes', 'required', 'email'],
            'status'   => ['sometimes', 'required', new Enum(UserStatus::class)],
            'per_page' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'page'     => ['sometimes', 'required', 'integer', 'min:1'],
        ];
    }
}
