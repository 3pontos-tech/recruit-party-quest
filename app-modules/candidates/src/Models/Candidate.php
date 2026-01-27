<?php

declare(strict_types=1);

namespace He4rt\Candidates\Models;

use App\Models\BaseModel;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Database\Factories\CandidateFactory;
use He4rt\Candidates\Policies\CandidatePolicy;
use He4rt\Location\Concerns\HasAddress;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property string $id
 * @property string $user_id
 * @property string|null $phone_number
 * @property string|null $headline
 * @property string|null $summary
 * @property Carbon|null $availability_date
 * @property bool $willing_to_relocate
 * @property bool $is_open_to_remote
 * @property float|null $expected_salary
 * @property string $expected_salary_currency
 * @property string|null $linkedin_url
 * @property string|null $portfolio_url
 * @property string|null $experience_level
 * @property Collection<int,string>|null $contact_links
 * @property string|null $self_identified_gender
 * @property bool $has_disability
 * @property string|null $source
 * @property bool $is_onboarded
 * @property Carbon|null $onboarding_completed_at
 * @property string|null $timezone
 * @property string $preferred_language
 * @property bool $data_consent_given
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Application> $applications
 *
 * @extends BaseModel<CandidateFactory>
 */
#[UsePolicy(CandidatePolicy::class)]
#[UseFactory(CandidateFactory::class)]
class Candidate extends BaseModel
{
    use HasAddress;
    use InteractsWithMedia;
    use SoftDeletes;

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return HasMany<Application, $this>
     */
    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    /**
     * @return HasMany<Education, $this>
     */
    public function degrees(): HasMany
    {
        return $this->hasMany(Education::class, 'candidate_id', 'id');
    }

    /**
     * @return HasMany<WorkExperience, $this>
     */
    public function workExperiences(): HasMany
    {
        return $this->hasMany(WorkExperience::class);
    }

    /**
     * @return array{years:int, months:int}
     */
    public function totalExperienceTime(): array
    {
        $totalMonths = (int) $this->workExperiences
            ->sum(function (WorkExperience $exp) {
                $end = $exp->is_currently_working_here
                    ? now()
                    : $exp->end_date;

                return $exp->start_date->diffInMonths($end);
            });

        $years = intdiv($totalMonths, 12);
        $months = $totalMonths % 12;

        return [
            'years' => $years,
            'months' => $months,
        ];
    }

    /**
     * @return BelongsToMany<Skill, $this, CandidateSkill>
     */
    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'candidate_known_skills', 'candidate_id', 'skill_id')
            ->withPivot(['years_of_experience', 'proficiency_level'])
            ->using(CandidateSkill::class);
    }

    public function hasCompletedOnboarding(): bool
    {
        return $this->is_onboarded;
    }

    protected function casts(): array
    {
        return [
            'willing_to_relocate' => 'boolean',
            'is_open_to_remote' => 'boolean',
            'contact_links' => 'array',
            'has_disability' => 'boolean',
            'availability_date' => 'date',
            'expected_salary' => 'decimal:2',
            'is_onboarded' => 'boolean',
            'data_consent_given' => 'boolean',
            'onboarding_completed_at' => 'datetime',
        ];
    }
}
