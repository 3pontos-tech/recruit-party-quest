<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Schemas;

use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

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
                        Tab::make('Overview')
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

                        Tab::make('Experience')
                            ->schema([
                                ViewEntry::make('work_experience')
                                    ->view('panel-organization::components.applications.tabs.work-experience'),
                            ]),

                        Tab::make('Screening')
                            ->schema([
                                ViewEntry::make('screening_responses')
                                    ->view('panel-organization::components.applications.tabs.screening-responses'),
                            ]),

                        Tab::make('Evaluations')
                            ->schema([
                                // Placeholder for future evaluations components
                                ViewEntry::make('evaluations_placeholder')
                                    ->view('panel-organization::components.applications.tabs.screening-responses')
                                    ->label('Interview Evaluations (Coming Soon)'),
                            ]),

                        Tab::make('Activities')
                            ->schema([
                                // Placeholder for future activities components
                                ViewEntry::make('activities_placeholder')
                                    ->view('panel-organization::components.applications.tabs.screening-responses')
                                    ->label('Application Activities (Coming Soon)'),
                            ]),
                    ]),
                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        // Pipeline Progress
                        ViewEntry::make('pipeline_progress')
                            ->view('panel-organization::components.applications.sidebar.pipeline-progress'),

                        // AI Match Score
                        ViewEntry::make('ai_match_score')
                            ->view('panel-organization::components.applications.sidebar.ai-match-score'),

                        // Quick Actions
                        ViewEntry::make('quick_actions')
                            ->view('panel-organization::components.applications.sidebar.quick-actions'),

                        // Candidate Snapshot
                        ViewEntry::make('candidate_snapshot')
                            ->view('panel-organization::components.applications.sidebar.candidate-snapshot'),

                        // Documents List
                        ViewEntry::make('documents_list')
                            ->view('panel-organization::components.applications.sidebar.documents-list'),

                        // Evaluation Summary
                        ViewEntry::make('evaluation_summary')
                            ->view('panel-organization::components.applications.sidebar.evaluation-summary'),
                    ]),
            ]);
    }
}
