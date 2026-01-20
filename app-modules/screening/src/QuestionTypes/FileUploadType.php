<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use He4rt\Screening\Contracts\QuestionTypeContract;
use He4rt\Screening\Enums\FileExtensionEnum;
use He4rt\Screening\Enums\QuestionTypeEnum;
use He4rt\Screening\QuestionTypes\Settings\FileUploadSettings;

/**
 * File upload question type - document/file uploads with size and type restrictions.
 */
final class FileUploadType implements QuestionTypeContract
{
    public static function type(): QuestionTypeEnum
    {
        return QuestionTypeEnum::FileUpload;
    }

    public static function label(): string
    {
        return __('screening::question_types.file_upload.label');
    }

    public static function icon(): string
    {
        return 'heroicon-o-paper-clip';
    }

    public static function settingsSchema(): array
    {
        return [
            TextInput::make('settings.max_size_kb')
                ->label(__('screening::question_types.file_upload.settings.max_size_kb'))
                ->numeric()
                ->minValue(1)
                ->suffix('KB')
                ->placeholder('5120')
                ->helperText(__('screening::question_types.file_upload.settings.max_size_help')),

            TextInput::make('settings.max_files')
                ->label(__('screening::question_types.file_upload.settings.max_files'))
                ->numeric()
                ->minValue(1)
                ->maxValue(10)
                ->default(1)
                ->placeholder('1'),

            Select::make('settings.allowed_extensions')
                ->label(__('screening::question_types.file_upload.settings.allowed_extensions'))
                ->options(FileExtensionEnum::class)
                ->multiple()
                ->columnSpanFull(),
        ];
    }

    public static function settingsClass(): string
    {
        return FileUploadSettings::class;
    }

    public static function defaultSettings(): FileUploadSettings
    {
        return new FileUploadSettings();
    }

    public static function component(): string
    {
        return 'screening::questions.file-upload';
    }
}
