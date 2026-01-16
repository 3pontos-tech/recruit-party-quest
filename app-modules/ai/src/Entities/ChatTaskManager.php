<?php

declare(strict_types=1);

namespace He4rt\Ai\Entities;

use He4rt\Ai\Contracts\HasGuideline;

final readonly class ChatTaskManager implements HasGuideline
{
    /**
     * @param  array<string, mixed>  $payload  Request Payload ['question': 'answer']
     */
    public function __construct(public array $payload) {}

    public static function make(array $payload): self
    {

        return new self(collect($payload)
            ->map(fn ($value) => match (true) {
                is_bool($value) => $value ? 'Y' : 'N',
                is_array($value) => implode(', ', $value),
                default => $value,
            })->toArray());
    }

    public function getQuestions(): array
    {
        return array_keys($this->payload);
    }

    public function getAnswers(): array
    {
        return array_filter($this->payload, fn ($value) => ! is_null($value));
    }

    public function getProgress(): int
    {
        return count($this->getAnswers()) / count($this->getQuestions());
    }

    public function isTaskComplete(): bool
    {
        return count($this->getAnswers()) === count($this->getQuestions());
    }

    public function getGuideline(): string
    {

        $tasks = collect($this->payload)
            ->map(fn ($value, $key) => sprintf('- [%s] %s: %s ', is_null($value) ? ' ' : 'x', $key, $value ?? '-'))
            ->implode("\n");

        return <<<GUIDELINE
        #### Lista items para serem resolvidos:

        {$tasks}
        GUIDELINE;
    }
}
