<?php

declare(strict_types=1);

namespace App\Validation;

final class Username
{
    public const string PATTERN = '/^\w+$/';

    public const int MAX_LENGTH = 40;

    /**
     * The shared username rule set, excluding presence and uniqueness.
     *
     * @return array<int, string>
     */
    public static function strict(): array
    {
        return ['string', 'max:'.self::MAX_LENGTH, 'regex:'.self::PATTERN];
    }

    public static function message(): string
    {
        return 'The :attribute may only contain letters, numbers and underscores.';
    }
}
