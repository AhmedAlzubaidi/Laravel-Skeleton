<?php

declare(strict_types=1);

namespace App\Queries;

use App\Foundation\BaseData;
use Spatie\LaravelData\Attributes\MapInputName;

class GetUsersQuery extends BaseData
{
    public function __construct(
        public ?string $username,
        public ?string $email,
        #[MapInputName('per_page')]
        public int $perPage = 10,
        public int $page = 1,
    ) {}

    /**
     * @return array<string, array<int, mixed>>
     */
    public static function rules(): array
    {
        return [
            'username' => ['sometimes', 'required', 'string', 'max:40'],
            'email'    => ['sometimes', 'required', 'email'],
            'per_page' => ['sometimes', 'required', 'integer', 'min:1', 'max:100'],
            'page'     => ['sometimes', 'required', 'integer', 'min:1'],
        ];
    }
}
