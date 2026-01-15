<?php

declare(strict_types=1);

namespace He4rt\Candidates\Enums;

use Filament\Support\Colors\Color;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;
use Filament\Support\Icons\Heroicon;

enum CandidateSkillCategory: string implements HasColor, HasLabel
{
    case Language = 'language';
    case SoftSkill = 'soft_skill';
    case HardSkill = 'hard_skill';

    public function getColor(): array
    {
        return match ($this) {
            self::Language => Color::Cyan,
            self::SoftSkill => Color::Blue,
            self::HardSkill => Color::Red,
        };
    }

    public function getIcon(): Heroicon
    {
        return match ($this) {
            self::Language => Heroicon::Language,
            self::SoftSkill => Heroicon::SpeakerWave,
            self::HardSkill => Heroicon::BookOpen,
        };
    }

    public function getLabel(): string
    {
        return __('candidate::skill_category.'.$this->value.'.label');
    }
}
