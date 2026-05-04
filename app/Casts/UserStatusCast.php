<?php

declare(strict_types=1);

namespace App\Casts;

use App\Enums\UserStatus;
use InvalidArgumentException;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

/**
 * @implements CastsAttributes<UserStatus, UserStatus|string>
 */
final class UserStatusCast implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): UserStatus
    {
        if ($value instanceof UserStatus) {
            return $value;
        }

        $status = is_string($value) ? UserStatus::tryFrom($value) : null;

        if ($status instanceof UserStatus) {
            return $status;
        }

        Log::warning('Invalid user status read from database; falling back to SUSPENDED.', [
            'model'    => $model::class,
            'model_id' => $model->getKey(),
            'column'   => $key,
            'value'    => $value,
        ]);

        return UserStatus::SUSPENDED;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): string
    {
        if ($value instanceof UserStatus) {
            return $value->value;
        }

        $status = is_string($value) ? UserStatus::tryFrom($value) : null;

        if (! $status instanceof UserStatus) {
            throw new InvalidArgumentException(sprintf(
                'Invalid user status "%s"; expected one of: %s.',
                is_scalar($value) ? (string) $value : get_debug_type($value),
                implode(', ', UserStatus::values()),
            ));
        }

        return $status->value;
    }
}
