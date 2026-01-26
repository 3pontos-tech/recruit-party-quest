<?php

declare(strict_types=1);

namespace He4rt\Screening\Enums;

use App\Enums\Concerns\StringifyEnum;
use Filament\Support\Contracts\HasLabel;
use He4rt\Screening\Contracts\HasValidations;
use He4rt\Screening\QuestionTypes\Settings\FileUploadSettings;
use He4rt\Screening\QuestionTypes\Settings\MultipleChoiceSettings;
use He4rt\Screening\QuestionTypes\Settings\NumberSettings;
use He4rt\Screening\QuestionTypes\Settings\SingleChoiceSettings;
use He4rt\Screening\QuestionTypes\Settings\TextSettings;
use He4rt\Screening\QuestionTypes\Settings\YesNoSettings;

enum QuestionTypeEnum: string implements HasLabel
{
    use StringifyEnum;

    case YesNo = 'yes_no';
    case Text = 'text';
    case Number = 'number';
    case SingleChoice = 'single_choice';
    case MultipleChoice = 'multiple_choice';
    case FileUpload = 'file_upload';

    public function getLabel(): string
    {
        return __('screening::enums.question_type.'.$this->value.'.label');
    }

    /**
     * @param  array<string, mixed>  $settings
     */
    public function settings(array $settings): HasValidations
    {
        return match ($this) {
            self::MultipleChoice => MultipleChoiceSettings::fromArray($settings),
            self::FileUpload => FileUploadSettings::fromArray($settings),
            self::Number => NumberSettings::fromArray($settings),
            self::SingleChoice => SingleChoiceSettings::fromArray($settings),
            self::Text => TextSettings::fromArray($settings),
            self::YesNo => YesNoSettings::fromArray($settings),
        };
    }
}
