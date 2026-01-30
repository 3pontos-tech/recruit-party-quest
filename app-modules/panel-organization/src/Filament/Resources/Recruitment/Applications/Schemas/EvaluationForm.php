<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use He4rt\Applications\Models\Application;
use He4rt\Feedback\Enums\EvaluationRatingEnum;

final class EvaluationForm
{
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
                ->label('Overall rating')
                ->required(),
            KeyValue::make('criteria_scores')
                ->default($criteriaFields)
                ->formatStateUsing(fn ($state) => blank($state) ? $criteriaFields : $state)
                ->keyPlaceholder('tanto faz')
                ->columnSpanFull()
                ->addable(false)
                ->deletable(false)
                ->editableKeys(false)
                ->label('Scores'),
            Textarea::make('comments')
                ->label('comments')
                ->placeholder('comments'),
            Textarea::make('recommendation')
                ->label('Recommendation')
                ->placeholder('Recommendations'),
            Textarea::make('strengths')
                ->label('Strengths')
                ->placeholder('Strengths'),
            Textarea::make('concerns')
                ->label('Concerns')
                ->placeholder('Concerns'),
        ];
    }
}
