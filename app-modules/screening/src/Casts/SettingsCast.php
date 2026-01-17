<?php

declare(strict_types=1);

namespace He4rt\Screening\Casts;

use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\QuestionTypes\QuestionTypeRegistry;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Custom cast that resolves settings to the appropriate ValueObject based on question_type.
 *
 * @implements CastsAttributes<object|null, array<string, mixed>|object|null>
 */
class SettingsCast implements CastsAttributes
{
    /**
     * Cast the given value from the database.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): ?object
    {
        if ($value === null || ! isset($attributes['question_type'])) {
            return null;
        }

        $type = $attributes['question_type'] instanceof QuestionTypeEnum
            ? $attributes['question_type']
            : QuestionTypeEnum::from($attributes['question_type']);

        $typeClass = QuestionTypeRegistry::get($type);
        $settingsClass = $typeClass::settingsClass();

        $data = is_string($value) ? json_decode($value, true) : $value;

        return $settingsClass::fromArray($data ?? []);
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        // Accept both array and ValueObject
        if (is_array($value)) {
            return json_encode($value);
        }

        // ValueObject with toArray() method
        if (method_exists($value, 'toArray')) {
            return json_encode($value->toArray());
        }

        return json_encode((array) $value);
    }
}
