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
                // Candidate Header - Full width
                ViewEntry::make('header')
                    ->view('panel-organization::components.applications.candidate-header')
                    ->columnSpanFull(),
                Tabs::make('application_tabs')
                    ->columnSpan(3)
                    ->schema([
                        Tab::make('Overview')
                            ->schema([
                                ViewEntry::make('basic_info')
                                    ->view('panel-organization::components.applications.candidate-contact-info'),
                            ]),
                    ]),
                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        // AI Match Score
                        ViewEntry::make('ai_match_score')
                            ->view('panel-organization::components.applications.sidebar.ai-match-score'),

                        // Quick Actions
                        ViewEntry::make('quick_actions')
                            ->view('panel-organization::components.applications.sidebar.quick-actions'),

                        // Pipeline Progress
                        ViewEntry::make('pipeline_progress')
                            ->view('panel-organization::components.applications.sidebar.pipeline-progress'),

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
