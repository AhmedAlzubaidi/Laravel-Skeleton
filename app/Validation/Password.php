<?php

declare(strict_types=1);

namespace App\Validation;

use Illuminate\Validation\Rules\Password as BasePassword;

/**
 * Application-specific extension of Laravel's Password rule.
 *
 * Adds a single named factory, Password::strong(), exposing the project's
 * canonical password complexity policy. Inherits every other capability of
 * the framework's Password rule, so callers can still chain ->min(...) /
 * ->mixedCase() / etc. when they need a different shape.
 */
final class Password extends BasePassword
{
    /**
     * The application's standard password complexity rule.
     *
     * Min 8 characters, mixed case, numbers, symbols, and absent from the
     * HaveIBeenPwned breach database.
     */
    public static function strong(): self
    {
        return self::min(8)
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();
    }
}
