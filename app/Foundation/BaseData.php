<?php

declare(strict_types=1);

namespace App\Foundation;

use Spatie\LaravelData\Data;

/**
 * Base class for all Commands, Queries, and DTOs.
 *
 * This class ensures toArray() method is final and behaves as laravel FormRequest validated() method.
 */
abstract class BaseData extends Data
{
    /**
     * Get the validation rules for the data object.
     *
     * @return array<string, string|array<int, mixed>>
     */
    public static function rules(): array
    {
        return [];
    }

    /**
     * Get the instance as an array.
     * Behave as laravel FormRequest validated() method.
     *
     * @return array<string, mixed>
     */
    public final function toArray(): array
    {
        $rules = static::rules();
        $data = parent::toArray();

        foreach ($data as $key => $value) {
            if (
                isset($rules[$key]) &&
                $this->hasValidationRule($rules[$key], ['required', 'sometimes']) &&
                ! filled($value)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Check if a given rule set includes any of the specified rule names.
     */
    protected function hasValidationRule(string|array $ruleSet, array $needles): bool
    {
        $rules = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;

        return ! empty(array_intersect(
            array_filter($rules, 'is_scalar'),
            $needles
        ));
    }
}
