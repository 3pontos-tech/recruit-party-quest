<?php

declare(strict_types=1);

namespace He4rt\Candidates\DTOs;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * Dados acessórios de uma experiência profissional, extraídos do currículo.
 *
 * Deliberadamente plano: o objeto vive apenas do Eloquent para a apresentação —
 * DTOs e state de formulário trabalham com primitivos, então ele nunca cruza a
 * serialização do Livewire.
 *
 * @implements Arrayable<string, list<string>>
 */
final readonly class WorkExperienceMetadata implements Arrayable, JsonSerializable
{
    /**
     * @param  list<string>  $skills
     */
    public function __construct(
        public array $skills = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $skills = $data['skills'] ?? [];

        if (! is_array($skills)) {
            return new self();
        }

        return new self(
            skills: array_values(array_filter(
                array_map(fn (mixed $skill): string => is_scalar($skill) ? mb_trim((string) $skill) : '', $skills),
                fn (string $skill): bool => $skill !== '',
            )),
        );
    }

    /**
     * @return array{skills: list<string>}
     */
    public function toArray(): array
    {
        return ['skills' => $this->skills];
    }

    /**
     * @return array{skills: list<string>}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
