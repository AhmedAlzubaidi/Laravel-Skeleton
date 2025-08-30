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
     * Behave similar to laravel FormRequest validated() method.
     * It filters out attributes that are not required and have no value.
     *
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        $rules = static::rules();
        $data = parent::toArray();

        foreach ($data as $key => $value) {
            if ($this->attributeShouldBeRemoved($rules, $key, $value)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Check if an attribute should be removed from the data.
     *
     * @param  array<string, string|array<int, mixed>>  $rules
     */
    private function attributeShouldBeRemoved(array $rules, string $key, mixed $value): bool
    {
        return ! isset($rules[$key]) ||
        $this->hasValidationRule($rules[$key], ['sometimes']) &&
        ! request()->filled($key) &&
        ! filled($value);
    }

    /**
     * Check if a given rule set includes any of the specified rule names.
     *
     * @param  string|array<int, mixed>  $ruleSet
     * @param  array<int, string>  $needles
     */
    private function hasValidationRule(string|array $ruleSet, array $needles): bool
    {
        $rules = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;

        return array_intersect(
            array_filter($rules, 'is_scalar'),
            $needles
        ) !== [];
    }
}
