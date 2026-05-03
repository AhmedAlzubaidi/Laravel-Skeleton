<?php

declare(strict_types=1);

namespace App\Validation;

use Illuminate\Validation\Rules\Password as BasePassword;

final class Password extends BasePassword
{
    public static function strong(): self
    {
        return self::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();
    }
}
