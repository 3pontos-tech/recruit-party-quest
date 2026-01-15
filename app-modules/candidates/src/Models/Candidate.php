<?php

declare(strict_types=1);

namespace He4rt\Candidates\Models;

use App\Models\BaseModel;
use He4rt\Candidates\Database\Factories\CandidateFactory;
use He4rt\Candidates\Policies\CandidatePolicy;
use He4rt\Location\HasAddress;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * @property string $id
 * @property string $user_id
 * @property bool $willing_to_relocate
 * @property string $experience_level
 * @property Collection<int,string>|null $contact_links
 * @property string $self_identified_gender
 * @property bool $has_disability
 * @property string $source
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @extends BaseModel<CandidateFactory>
 */
#[UsePolicy(CandidatePolicy::class)]
#[UseFactory(CandidateFactory::class)]
class Candidate extends BaseModel
{
    use HasAddress;
    use SoftDeletes;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsToMany<Skill, $this, CandidateSkill>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'candidate_known_skills', 'candidate_id', 'skill_id')
            ->withPivot(['years_of_experience', 'proficiency_level'])
            ->using(CandidateSkill::class)
            ->withTimestamps();
    }

    protected function casts(): array
    {
        return [
            'willing_to_relocate' => 'boolean',
            'contact_links' => 'array',
            'has_disability' => 'boolean',
        ];
    }
}
