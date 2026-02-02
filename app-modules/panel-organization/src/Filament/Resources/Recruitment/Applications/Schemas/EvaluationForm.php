<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Schemas;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use He4rt\Applications\Models\Application;
use He4rt\Feedback\Enums\EvaluationRatingEnum;

final class EvaluationForm
{
    /**
     * @return array<int, Field>
     */
    public static function make(): array
    {
        $criteriaFields = [
            'technical_skills' => '',
            'communication' => '',
            'problem_solving' => '',
            'culture_fit' => '',
        ];

        return [
            Hidden::make('team_id')
                ->default(filament()->getTenant()->getKey()),
            Hidden::make('application_id')
                ->default(fn (Application $record) => $record->getKey()),
            Hidden::make('evaluator_id')
                ->default(auth()->user()->getKey()),
            Select::make('overall_rating')
                ->options(EvaluationRatingEnum::class)
                ->enum(EvaluationRatingEnum::class)
                ->label(__('panel-organization::filament.forms.overall_rating'))
                ->required(),
            KeyValue::make('criteria_scores')
                ->default($criteriaFields)
                ->formatStateUsing(fn ($state) => blank($state) ? $criteriaFields : $state)
                ->keyPlaceholder(__('panel-organization::filament.forms.criteria_key_placeholder'))
                ->columnSpanFull()
                ->addable(false)
                ->deletable(false)
                ->editableKeys(false)
                ->label(__('panel-organization::filament.forms.scores')),
            Textarea::make('comments')
                ->label(__('panel-organization::filament.forms.comments'))
                ->placeholder(__('panel-organization::filament.forms.comments_placeholder')),
            Textarea::make('recommendation')
                ->label(__('panel-organization::filament.forms.recommendation'))
                ->placeholder(__('panel-organization::filament.forms.recommendation_placeholder')),
            Textarea::make('strengths')
                ->label(__('panel-organization::filament.forms.strengths'))
                ->placeholder(__('panel-organization::filament.forms.strengths_placeholder')),
            Textarea::make('concerns')
                ->label(__('panel-organization::filament.forms.concerns'))
                ->placeholder(__('panel-organization::filament.forms.concerns_placeholder')),
        ];
    }
}
