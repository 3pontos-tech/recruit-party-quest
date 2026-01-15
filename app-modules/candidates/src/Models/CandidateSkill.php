<?php

declare(strict_types=1);

namespace He4rt\Candidates\Models;

use App\Models\BasePivot;
use He4rt\Candidates\Database\Factories\CandidateSkillFactory;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $skill_id
 * @property string $candidate_id
 * @property int $proficiency_level
 * @property int $years_of_experience
 * @property-read string $formatted_skill_name
 * @property-read Skill $skill
 * @property-read Candidate $candidate
 *
 * @extends BasePivot<CandidateSkillFactory>
 */
#[UseFactory(CandidateSkillFactory::class)]
class CandidateSkill extends BasePivot
{
    protected $table = 'candidate_known_skills';

    /**
     * @return BelongsTo<Skill, $this>
     */
    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class, 'skill_id');
    }

    /**
     * @return BelongsTo<Candidate, $this>
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}
