<?php

declare(strict_types=1);

namespace He4rt\Ai\Enums;

use Filament\Support\Contracts\HasLabel;

enum AiModelApplicabilityFeature: string implements HasLabel
{
    case InstitutionalAdvisor = 'institutional_advisor';

    case CustomAdvisors = 'custom_advisors';

    case ResearchAdvisor = 'research_advisor';

    case QuestionAndAnswerAdvisor = 'question_and_answer_advisor';

    case IntegratedAdvisor = 'integrated_advisor';

    public static function parse(string|self|null $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        return self::tryFrom($value);
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::InstitutionalAdvisor => 'Institutional Advisor',
            self::CustomAdvisors => 'Custom Advisors',
            self::ResearchAdvisor => 'Research Advisor',
            self::IntegratedAdvisor => 'Integrated Advisor',
            default => 'Fodase',
        };
    }

    /**
     * @return array<AiModel>
     */
    public function getModels(): array
    {
        return array_filter(
            AiModel::cases(),
            fn (AiModel $model): bool => in_array($this, $model->getApplicableFeatures()),
        );
    }

    /**
     * @return array<string, string>
     */
    public function getModelsAsSelectOptions(): array
    {
        return array_reduce(
            $this->getModels(),
            function (array $carry, AiModel $model): array {
                $carry[$model->value] = $model->getLabel();

                return $carry;
            },
            initial: [],
        );
    }
}
