<?php

declare(strict_types=1);

namespace He4rt\Screening\Presenters;

use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\Models\ScreeningResponse;
use He4rt\Screening\QuestionTypes\QuestionTypeRegistry;

/**
 * Formats a ScreeningResponse's value for display based on its question type.
 */
final readonly class ScreeningResponsePresenter
{
    public function __construct(
        private ScreeningResponse $response,
    ) {}

    /**
     * Get the formatted display value for the response.
     */
    public function displayValue(): string
    {
        $rawValue = $this->response->response_value['value'] ?? null;

        if ($rawValue === null) {
            return '—';
        }

        return match ($this->response->question->question_type) {
            QuestionTypeEnum::YesNo => $this->formatYesNo($rawValue),
            QuestionTypeEnum::Number => $this->formatNumber($rawValue),
            QuestionTypeEnum::SingleChoice => $this->formatSingleChoice($rawValue),
            QuestionTypeEnum::MultipleChoice => $this->formatMultipleChoice($rawValue),
            default => (string) $rawValue,
        };
    }

    /**
     * Get the icon for this question's type.
     */
    public function icon(): string
    {
        $typeClass = QuestionTypeRegistry::get($this->response->question->question_type);

        return $typeClass::icon();
    }

    /**
     * Get the human-readable label for this question type.
     */
    public function typeLabel(): string
    {
        return $this->response->question->question_type->getLabel();
    }

    /**
     * Whether this is a multiple choice response (renders as list).
     */
    public function isList(): bool
    {
        return $this->response->question->question_type === QuestionTypeEnum::MultipleChoice;
    }

    /**
     * Get the list items for a multiple choice response.
     *
     * @return array<int, array{value: string, label: string, selected: bool}>
     */
    public function listItems(): array
    {
        $selectedValues = (array) ($this->response->response_value['value'] ?? []);
        $settings = $this->response->question->settings;
        $choices = $settings['choices'] ?? [];

        return array_map(fn (array $choice): array => [
            'value' => $choice['value'],
            'label' => $choice['label'] ?? $choice['value'],
            'selected' => in_array($choice['value'], $selectedValues, true),
        ], $choices);
    }

    private function formatYesNo(mixed $value): string
    {
        return $value === 'yes'
            ? __('panel-organization::view.tabs.screening_responses.yes')
            : __('panel-organization::view.tabs.screening_responses.no');
    }

    private function formatNumber(mixed $value): string
    {
        $settings = $this->response->question->settings;
        $prefix = $settings['prefix'] ?? '';
        $suffix = $settings['suffix'] ?? '';

        $parts = array_filter([
            $prefix,
            (string) $value,
            $suffix,
        ]);

        return implode(' ', $parts);
    }

    private function formatSingleChoice(mixed $value): string
    {
        $settings = $this->response->question->settings;
        $choices = $settings['choices'] ?? [];

        foreach ($choices as $choice) {
            if (($choice['value'] ?? null) === $value) {
                return $choice['label'] ?? $value;
            }
        }

        return (string) $value;
    }

    private function formatMultipleChoice(mixed $value): string
    {
        $selectedValues = (array) $value;
        $settings = $this->response->question->settings;
        $choices = $settings['choices'] ?? [];

        $labels = [];
        foreach ($choices as $choice) {
            if (in_array($choice['value'] ?? null, $selectedValues, true)) {
                $labels[] = $choice['label'] ?? $choice['value'];
            }
        }

        return implode(', ', $labels);
    }
}
