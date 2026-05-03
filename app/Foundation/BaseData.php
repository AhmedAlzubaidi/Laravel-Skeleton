<?php

declare(strict_types=1);

namespace App\Foundation;

use Spatie\LaravelData\Data;

abstract class BaseData extends Data
{
    /**
     * @return array<string, string|array<int, mixed>>
     */
    public static function rules(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated(): array
    {
        $rules = static::rules();
        $validated = [];

        foreach (parent::toArray() as $key => $value) {
            $key = (string) $key;

            if (! $this->attributeShouldBeRemoved($rules, $key, $value)) {
                $validated[$key] = $value;
            }
        }

        return $validated;
    }

    /**
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
     * @param  string|array<int, mixed>  $ruleSet
     * @param  array<int, string>  $needles
     */
    private function hasValidationRule(string|array $ruleSet, array $needles): bool
    {
        $rules = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;

        return array_intersect(
            array_filter($rules, is_scalar(...)),
            $needles
        ) !== [];
    }
}
