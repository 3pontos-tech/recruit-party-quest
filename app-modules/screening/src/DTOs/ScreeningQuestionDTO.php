<?php

declare(strict_types=1);

namespace He4rt\Screening\DTOs;

use He4rt\Screening\Enums\QuestionTypeEnum;

/**
 * Data Transfer Object for screening question data.
 *
 * @phpstan-type KnockoutCriteriaArray array<string, mixed>
 */
readonly class ScreeningQuestionDTO
{
    /**
     * @param  array<string, mixed>|null  $settings
     * @param  KnockoutCriteriaArray|null  $knockoutCriteria
     */
    public function __construct(
        public string $screenableId,
        public string $screenableType,
        public string $teamId,
        public string $questionText,
        public QuestionTypeEnum $questionType,
        public int $displayOrder,
        public bool $isRequired = true,
        public bool $isKnockout = false,
        public ?array $settings = null,
        public ?array $knockoutCriteria = null,
        public ?string $id = null,
    ) {}

    /**
     * Create a DTO from an associative array.
     *
     * @param  array{
     *     screenable_id: string,
     *     screenable_type: string,
     *     team_id: string,
     *     question_text: string,
     *     question_type: string|QuestionTypeEnum,
     *     display_order: int,
     *     is_required?: bool,
     *     is_knockout?: bool,
     *     settings?: array<string, mixed>|null,
     *     knockout_criteria?: KnockoutCriteriaArray|null,
     *     id?: string|null,
     * }  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            screenableId: $data['screenable_id'],
            screenableType: $data['screenable_type'],
            teamId: $data['team_id'],
            questionText: $data['question_text'],
            questionType: $data['question_type'] instanceof QuestionTypeEnum
            ? $data['question_type']
            : QuestionTypeEnum::from($data['question_type']),
            displayOrder: (int) $data['display_order'],
            isRequired: $data['is_required'] ?? true,
            isKnockout: $data['is_knockout'] ?? false,
            settings: $data['settings'] ?? null,
            knockoutCriteria: $data['knockout_criteria'] ?? null,
            id: $data['id'] ?? null,
        );
    }

    /**
     * Convert repeater form data to a collection of DTOs.
     *
     * @param  array<int, array<string, mixed>>  $repeaterData
     * @return array<int, self>
     */
    public static function collectionFromRepeater(
        array $repeaterData,
        string $screenableId,
        string $screenableType,
        string $teamId,
    ): array {
        $dtos = [];

        foreach ($repeaterData as $index => $item) {
            $dtos[] = self::fromArray([
                'screenable_id' => $screenableId,
                'screenable_type' => $screenableType,
                'team_id' => $teamId,
                'question_text' => $item['question_text'],
                'question_type' => $item['question_type'],
                'display_order' => $item['display_order'] ?? $index + 1,
                'is_required' => (bool) ($item['is_required'] ?? true),
                'is_knockout' => (bool) ($item['is_knockout'] ?? false),
                'settings' => $item['settings'] ?? null,
                'knockout_criteria' => $item['knockout_criteria'] ?? null,
                'id' => $item['id'] ?? null,
            ]);
        }

        return $dtos;
    }

    /**
     * Convert DTO to array for model creation/update.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'screenable_id' => $this->screenableId,
            'screenable_type' => $this->screenableType,
            'team_id' => $this->teamId,
            'question_text' => $this->questionText,
            'question_type' => $this->questionType,
            'display_order' => $this->displayOrder,
            'is_required' => $this->isRequired,
            'is_knockout' => $this->isKnockout,
            'settings' => $this->settings,
            'knockout_criteria' => $this->knockoutCriteria,
        ];
    }

    /**
     * Check if this DTO represents an existing question (update) or a new one (create).
     */
    public function isExisting(): bool
    {
        return $this->id !== null;
    }
}
