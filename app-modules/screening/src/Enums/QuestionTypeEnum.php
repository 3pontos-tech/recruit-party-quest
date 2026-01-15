<?php

declare(strict_types=1);

namespace He4rt\Screening\Enums;

use Filament\Support\Contracts\HasLabel;

enum QuestionTypeEnum: string implements HasLabel
{
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
}
