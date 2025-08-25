<?php

declare(strict_types=1);

namespace App\Transformers;

use Illuminate\Support\Facades\Hash;
use Spatie\LaravelData\Support\DataProperty;
use Spatie\LaravelData\Transformers\Transformer;
use Spatie\LaravelData\Support\Transformation\TransformationContext;

class PasswordTransformer implements Transformer
{
    /**
     * Transform the password to a hash.
     */
    public function transform(DataProperty $property, mixed $value, TransformationContext $context): ?string
    {
        return filled($value) ? Hash::make($value) : null;
    }
}
