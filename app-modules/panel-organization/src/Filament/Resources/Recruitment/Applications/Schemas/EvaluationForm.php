<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;
use He4rt\Feedback\Enums\EvaluationRatingEnum;

final class EvaluationForm
{
    public static function section(): Section
    {
        return Section::make(__('panel-organization::filament.forms.evaluation_section.heading'))
            ->description(__('panel-organization::filament.forms.evaluation_section.description'))
            ->icon(Heroicon::Star)
            ->schema(self::make());
    }

    /**
     * @return array<int, Component>
     */
    public static function make(): array
    {
        $criteria = ['technical_skills', 'communication', 'problem_solving', 'culture_fit'];
        $scoreOptions = [1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'];

        return [
            Select::make('overall_rating')
                ->options(EvaluationRatingEnum::class)
                ->enum(EvaluationRatingEnum::class)
                ->label(__('panel-organization::filament.forms.overall_rating'))
                ->required(),
            Grid::make(2)->schema(array_map(
                fn (string $key): ToggleButtons => ToggleButtons::make('criteria_scores.'.$key)
                    ->label(__('panel-organization::view.tabs.feedbacks.criteria.'.$key))
                    ->options($scoreOptions)
                    ->inline()
                    ->required(),
                $criteria,
            )),
            Grid::make(2)->schema([
                Textarea::make('strengths')
                    ->label(__('panel-organization::filament.forms.strengths'))
                    ->placeholder(__('panel-organization::filament.forms.strengths_placeholder')),
                Textarea::make('concerns')
                    ->label(__('panel-organization::filament.forms.concerns'))
                    ->placeholder(__('panel-organization::filament.forms.concerns_placeholder')),
                Textarea::make('recommendation')
                    ->label(__('panel-organization::filament.forms.recommendation'))
                    ->placeholder(__('panel-organization::filament.forms.recommendation_placeholder')),
                Textarea::make('comments')
                    ->label(__('panel-organization::filament.forms.comments'))
                    ->placeholder(__('panel-organization::filament.forms.comments_placeholder')),
            ]),
        ];
    }
}
