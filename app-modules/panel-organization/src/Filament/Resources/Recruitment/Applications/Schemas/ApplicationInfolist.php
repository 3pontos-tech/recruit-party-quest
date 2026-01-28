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

                        // Evaluation Summary
                        //                        ViewEntry::make('evaluation_summary')
                        //                            ->view('panel-organization::components.applications.sidebar.evaluation-summary'),
                    ]),
            ]);
    }

    protected static function getAdvanceStageAction(): Action
    {
        return Action::make('advance_stage')
            ->label(__('panel-organization::filament.actions.advance_stage.label'))
            ->icon('heroicon-o-arrow-right-circle')
            ->color('primary')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->modalHeading(__('panel-organization::filament.actions.advance_stage.modal_heading'))
            ->modalDescription(__('panel-organization::filament.actions.advance_stage.modal_description'))
            ->schema([
                Textarea::make('test')
                    ->label(__('panel-organization::filament.fields.test_label'))
                    ->placeholder(__('panel-organization::filament.fields.test_placeholder'))
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                Notification::make()
                    ->title(__('panel-organization::filament.notifications.ok_title'))
                    ->body(__('panel-organization::filament.notifications.ok_body'))
                    ->success()
                    ->send();
            });
    }

    protected static function getScheduleInterviewAction(): Action
    {
        return Action::make('schedule_interview')
            ->label(__('panel-organization::filament.actions.schedule_interview.label'))
            ->icon('heroicon-o-calendar-days')
            ->color('success')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->modalHeading(__('panel-organization::filament.actions.schedule_interview.modal_heading'))
            ->modalDescription(__('panel-organization::filament.actions.schedule_interview.modal_description'))
            ->schema([
                Textarea::make('test')
                    ->label(__('panel-organization::filament.fields.test_label'))
                    ->placeholder(__('panel-organization::filament.fields.test_placeholder'))
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                Notification::make()
                    ->title(__('panel-organization::filament.notifications.ok_title'))
                    ->body(__('panel-organization::filament.notifications.ok_body'))
                    ->success()
                    ->send();
            });
    }

    protected static function getSendEmailAction(): Action
    {
        return Action::make('send_email')
            ->label(__('panel-organization::filament.actions.send_email.label'))
            ->icon('heroicon-o-envelope')
            ->color('info')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->outlined()
            ->modalHeading(__('panel-organization::filament.actions.send_email.modal_heading'))
            ->modalDescription(__('panel-organization::filament.actions.send_email.modal_description'))
            ->schema([
                Textarea::make('test')
                    ->label(__('panel-organization::filament.fields.test_label'))
                    ->placeholder(__('panel-organization::filament.fields.test_placeholder'))
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                Notification::make()
                    ->title(__('panel-organization::filament.notifications.ok_title'))
                    ->body(__('panel-organization::filament.notifications.ok_body'))
                    ->success()
                    ->send();
            });
    }

    protected static function getAddCommentAction(): Action
    {
        return Action::make('add_comment')
            ->label(__('panel-organization::filament.actions.add_comment.label'))
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->color('gray')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->outlined()
            ->modalHeading(__('panel-organization::filament.actions.add_comment.modal_heading'))
            ->modalDescription(__('panel-organization::filament.actions.add_comment.modal_description'))
            ->schema([
                Textarea::make('test')
                    ->label(__('panel-organization::filament.fields.test_label'))
                    ->placeholder(__('panel-organization::filament.fields.test_placeholder'))
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                Notification::make()
                    ->title(__('panel-organization::filament.notifications.ok_title'))
                    ->body(__('panel-organization::filament.notifications.ok_body'))
                    ->success()
                    ->send();
            });
    }

    protected static function getRejectApplicationAction(): Action
    {
        return Action::make('reject_application')
            ->label(__('panel-organization::filament.actions.reject_application.label'))
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->extraAttributes(fn () => ['class' => 'w-full'])
            ->outlined()
            ->requiresConfirmation()
            ->modalHeading(__('panel-organization::filament.actions.reject_application.modal_heading'))
            ->modalDescription(__('panel-organization::filament.actions.reject_application.modal_description'))
            ->schema([
                Textarea::make('test')
                    ->label(__('panel-organization::filament.fields.test_label'))
                    ->placeholder(__('panel-organization::filament.fields.test_placeholder'))
                    ->rows(3),
            ])
            ->action(function (array $data): void {
                Notification::make()
                    ->title(__('panel-organization::filament.notifications.ok_title'))
                    ->body(__('panel-organization::filament.notifications.ok_body'))
                    ->success()
                    ->send();
            });
    }
}
