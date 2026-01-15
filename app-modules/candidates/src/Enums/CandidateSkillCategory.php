<?php

declare(strict_types=1);

namespace He4rt\Candidates\Enums;

enum CandidateSkillCategory: string
{
    case Language = 'language';
    case SoftSkill = 'soft_skill';
    case HardSkill = 'hard_skill';
}
