<?php

declare(strict_types=1);

namespace He4rt\Organization\Filament\Resources\Recruitment\Applications\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\ViewEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
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
                    ]),
                Grid::make(1)
                    ->columnSpan(1)
                    ->schema([
                        // Quick Actions
                        Section::make('Quick Actions')
                            ->icon('heroicon-o-bolt')
                            ->schema([
                                Actions::make([
                                    self::getAdvanceStageAction(),
                                    self::getScheduleInterviewAction(),
                                    self::getSendEmailAction(),
                                    self::getAddCommentAction(),
                                    self::getRejectApplicationAction(),
                                ]),
                            ]),
                        // Pipeline Progress
                        ViewEntry::make('pipeline_progress')
                            ->view('panel-organization::components.applications.sidebar.pipeline-progress'),

                        // AI Match Score
                        //                        ViewEntry::make('ai_match_score')
                        //                            ->view('panel-organization::components.applications.sidebar.ai-match-score'),

                        // Documents List
                        //                        ViewEntry::make('documents_list')
                        //                            ->view('panel-organization::components.applications.sidebar.documents-list'),

                        // Evaluation Summary
                        //                        ViewEntry::make('evaluation_summary')
                        //                            ->view('panel-organization::components.applications.sidebar.evaluation-summary'),
                    ]),
            ]);
    }

    protected static function getAdvanceStageAction(): Action
    {
        return Action::make('advance_stage')
            ->label('Advance Stage')
            ->icon('heroicon-o-arrow-right-circle')
            ->color('primary')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->modalHeading('Advance to Next Stage')
            ->modalDescription('Are you sure you want to advance this candidate to the next recruitment stage?')
            ->schema([
                Textarea::make('test')
                    ->label('test')
                    ->placeholder('testandooooooooo')
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                Notification::make()
                    ->title('é isso ai')
                    ->body('testandoooooooooooo')
                    ->success()
                    ->send();
            });
    }

    protected static function getScheduleInterviewAction(): Action
    {
        return Action::make('schedule_interview')
            ->label('Schedule Interview')
            ->icon('heroicon-o-calendar-days')
            ->color('success')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->modalHeading('Schedule Interview')
            ->modalDescription('Schedule an interview appointment with the candidate.')
            ->schema([
                Textarea::make('test')
                    ->label('test')
                    ->placeholder('testandooooooooo')
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                Notification::make()
                    ->title('é isso ai')
                    ->body('testandoooooooooooo')
                    ->success()
                    ->send();
            });
    }

    protected static function getSendEmailAction(): Action
    {
        return Action::make('send_email')
            ->label('Send Email')
            ->icon('heroicon-o-envelope')
            ->color('info')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->outlined()
            ->modalHeading('Send Email to Candidate')
            ->modalDescription('Send a personalized email to the candidate.')
            ->schema([
                Textarea::make('test')
                    ->label('test')
                    ->placeholder('testandooooooooo')
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                Notification::make()
                    ->title('é isso ai')
                    ->body('testandoooooooooooo')
                    ->success()
                    ->send();
            });
    }

    protected static function getAddCommentAction(): Action
    {
        return Action::make('add_comment')
            ->label('Add Internal Comment')
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->color('gray')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->outlined()
            ->modalHeading('Add Internal Comment')
            ->modalDescription('Add a note that will only be visible to recruiters and administrators.')
            ->schema([
                Textarea::make('test')
                    ->label('test')
                    ->placeholder('testandooooooooo')
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                Notification::make()
                    ->title('é isso ai')
                    ->body('testandoooooooooooo')
                    ->success()
                    ->send();
            });
    }

    protected static function getRejectApplicationAction(): Action
    {
        return Action::make('reject_application')
            ->label('Reject Application')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->outlined()
            ->requiresConfirmation()
            ->modalHeading('Reject Application')
            ->modalDescription('This action cannot be undone. The candidate will be notified of the rejection.')
            ->schema([
                Textarea::make('test')
                    ->label('test')
                    ->placeholder('testandooooooooo')
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                Notification::make()
                    ->title('é isso ai')
                    ->body('testandoooooooooooo')
                    ->success()
                    ->send();
            });
    }
}
