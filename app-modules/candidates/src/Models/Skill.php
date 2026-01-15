<?php

declare(strict_types=1);

namespace He4rt\Candidates\Models;

use App\Models\BaseModel;
use He4rt\Candidates\Database\Factories\SkillFactory;
use He4rt\Candidates\Enums\CandidateSkillCategory;
use He4rt\Candidates\Policies\SkillPolicy;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $name
 * @property CandidateSkillCategory $category
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon|null $deleted_at
 *
 * @extends BaseModel<SkillFactory>
 */
#[UsePolicy(SkillPolicy::class)]
#[UseFactory(SkillFactory::class)]
class Skill extends BaseModel
{
    use SoftDeletes;

    protected $table = 'candidate_skills';

    /**
     * @return BelongsToMany<Candidate, $this, CandidateSkill>
     */
    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(Candidate::class, 'candidate_known_skills', 'skill_id', 'candidate_id')
            ->withPivot(['years_of_experience', 'proficiency_level'])
            ->using(CandidateSkill::class)
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'category' => CandidateSkillCategory::class,
        ];
    }
}
