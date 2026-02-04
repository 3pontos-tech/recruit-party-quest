<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Schemas;

use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Actions\CommentApplicationAction;
use He4rt\Organization\Filament\Resources\Recruitment\Applications\Actions\RejectApplicationAction;
use He4rt\Organization\Filament\Resources\Recruitment\JobRequisitions\Pages\Kanban\Actions\StateTransitionAction;

class ApplicationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(4)
            ->components([
                ViewEntry::make('header')
                    ->view('panel-organization::components.applications.candidate-header')
                    ->columnSpanFull(),

                Tabs::make('application_tabs')
                    ->columnSpan(3)
                    ->schema([
                        Tab::make(__('panel-organization::filament.tabs.overview'))
                            ->schema([
                                ViewEntry::make('cover_letter')
                                    ->view('panel-organization::components.applications.tabs.cover-letter'),

                                ViewEntry::make('skills_proficiency')
                                    ->view('panel-organization::components.applications.tabs.skills-proficiency'),

                                ViewEntry::make('professional_summary')
                                    ->view('panel-organization::components.applications.tabs.professional-summary'),

                                ViewEntry::make('education')
                                    ->view('panel-organization::components.applications.tabs.education'),
                            ]),

                        Tab::make(__('panel-organization::filament.tabs.experience'))
                            ->schema([
                                ViewEntry::make('work_experience')
                                    ->view('panel-organization::components.applications.tabs.work-experience'),
                            ]),
                    ]),
                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        // Quick Actions
                        Section::make(__('panel-organization::filament.section.quick_actions'))
                            ->icon('heroicon-o-bolt')
                            ->schema([
                                Actions::make([
                                    StateTransitionAction::make(),
                                    CommentApplicationAction::make(),
                                    RejectApplicationAction::make(),
                                ]),
                            ]),
                        // Pipeline Progress
                        ViewEntry::make('pipeline_progress')
                            ->view('panel-organization::components.applications.sidebar.pipeline-progress'),

                        // AI Match Score
                        //                        ViewEntry::make('ai_match_score')
                        //                            ->view('panel-organization::components.applications.sidebar.ai-match-score'),

                        // Evaluation Summary
                        //                        ViewEntry::make('evaluation_summary')
                        //                            ->view('panel-organization::components.applications.sidebar.evaluation-summary'),
                    ]),
            ]);
    }
}
