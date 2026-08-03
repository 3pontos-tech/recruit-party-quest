<?php

declare(strict_types=1);

namespace He4rt\Candidates\Casts;

use He4rt\Candidates\DTOs\WorkExperienceMetadata;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

/**
 * Custom casts são invocados mesmo com valor nulo, então `get()` sempre devolve
 * uma instância — o consumidor lê `$experience->metadata->skills` sem `?->`.
 *
 * @implements CastsAttributes<WorkExperienceMetadata, WorkExperienceMetadata|array<string, mixed>>
 */
final class AsWorkExperienceMetadata implements CastsAttributes
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): WorkExperienceMetadata
    {
        $data = is_string($value) ? json_decode($value, true) : $value;

        return WorkExperienceMetadata::fromArray(is_array($data) ? $data : []);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        $data = $value instanceof WorkExperienceMetadata
            ? $value->toArray()
            : WorkExperienceMetadata::fromArray((array) $value)->toArray();

        return json_encode($data, JSON_THROW_ON_ERROR);
    }
}
