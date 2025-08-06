<?php

declare(strict_types=1);

namespace App\Foundation;

use Spatie\LaravelData\Data;

/**
 * Base class for all Commands, Queries, and DTOs.
 * 
 * This class provides common functionality and ensures consistent behavior
 * across all data transfer objects in the application.
 */
abstract class BaseData extends Data
{
    /**
     * Get the instance as an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $rules = static::rules();
        $data = parent::toArray();

        foreach ($data as $key => $value) {
            if (
                isset($rules[$key]) &&
                $this->hasValidationRule($rules[$key], ['required', 'sometimes']) &&
                !filled($value)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /**
     * Check if a given rule set includes any of the specified rule names.
     *
     * @param string|array $ruleSet
     * @param array $needles
     * @return bool
     */
    protected function hasValidationRule(string|array $ruleSet, array $needles): bool
    {
        $rules = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
        return !empty(array_intersect(
            array_filter($rules, 'is_scalar'),
            $needles
        ));
    }

    /**
     * Get the validation rules for the data object.
     *
     * @return array<string, string|array<int, mixed>>
     */
    public static function rules(): array
    {
        return [];
    }
}
