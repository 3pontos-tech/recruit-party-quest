<?php

declare(strict_types=1);

namespace He4rt\Candidates\Models;

use App\Models\BaseModel;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Database\Factories\CandidateFactory;
use He4rt\Candidates\Policies\CandidatePolicy;
use He4rt\Location\Concerns\HasAddress;
use He4rt\Recruitment\Requisitions\Enums\ExperienceLevelEnum;
use He4rt\Users\User;
use Illuminate\Database\Eloquent\Attributes\UseFactory;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Tags\HasTags;

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
 * @property ExperienceLevelEnum|null $experience_level
 * @property string|null $self_identified_gender
 * @property bool $has_disability
 * @property string|null $source
 * @property bool $is_onboarded
 * @property Carbon|null $onboarding_completed_at
 * @property Carbon|null $cv_last_uploaded_at
 * @property string|null $timezone
 * @property string $preferred_language
 * @property bool $data_consent_given
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property int $total_experience_months
 * @property string $total_experience_formatted
 * @property int $profile_completion_percentage
 * @property-read User $user
 * @property-read Collection<int, Application> $applications
 *
 * @extends BaseModel<CandidateFactory>
 */
#[UsePolicy(CandidatePolicy::class)]
#[UseFactory(CandidateFactory::class)]
class Candidate extends BaseModel implements HasMedia
{
    use HasAddress;
    use HasTags;
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
     * @return HasMany<CandidateJobSaved, $this>
     */
    public function bookmarkedJobs(): HasMany
    {
        return $this->hasMany(CandidateJobSaved::class)->latest();
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

    public function getExperienceDuration(WorkExperience $experience): string
    {
        $end = $experience->is_currently_working_here
            ? now()
            : ($experience->end_date ?? now());

        $months = (float) $experience->start_date->diffInMonths($end);

        return $this->formatExperienceTime($months);
    }

    public function calculateProfileCompletion(): int
    {
        $sections = $this->getProfileSections();
        $totalCompletion = 0;

        foreach ($sections as $section) {
            $completedFields = count(array_filter($section['fields']));
            $totalFields = count($section['fields']);
            $sectionCompletion = $totalFields > 0 ? ($completedFields / $totalFields) : 0;
            $totalCompletion += $sectionCompletion * $section['weight'];
        }

        return (int) round($totalCompletion);
    }

    /**
     * @return array<string, array{weight: int, completed: bool, label: string}>
     */
    public function getMissingProfileSections(): array
    {
        $sections = $this->getProfileSections();
        $missing = [];

        foreach ($sections as $key => $section) {
            $completedFields = count(array_filter($section['fields']));
            $totalFields = count($section['fields']);
            $isComplete = $completedFields === $totalFields;

            if (! $isComplete) {
                $missing[$key] = [
                    'weight' => $section['weight'],
                    'completed' => $isComplete,
                    'label' => $section['label'],
                ];
            }
        }

        return $missing;
    }

    public function isCvUploadOnCooldown(): bool
    {
        if ($this->cv_last_uploaded_at === null) {
            return false;
        }

        return $this->cv_last_uploaded_at->diffInDays(now()) < 3;
    }

    public function cvCooldownDaysRemaining(): int
    {
        if ($this->cv_last_uploaded_at === null) {
            return 0;
        }

        return max(0, 3 - (int) $this->cv_last_uploaded_at->diffInDays(now()));
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
            ->singleFile()
            ->acceptsFile(fn (File $file): bool => in_array(
                $file->mimeType,
                ['image/jpeg', 'image/png', 'image/webp'],
                true
            ));
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->performOnCollections('avatar')
            ->width(200)
            ->height(200);
    }

    /** @return Attribute<int, void> */
    protected function totalExperienceMonths(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->calculateTotalExperienceMonths()
        );
    }

    /**
     * @return Attribute<string, string>
     */
    protected function totalExperienceFormatted(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->formatExperienceTime($this->total_experience_months)
        );
    }

    /**
     * @return Attribute<int, void>
     */
    protected function profileCompletionPercentage(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->calculateProfileCompletion()
        );
    }

    protected function casts(): array
    {
        return [
            'experience_level' => ExperienceLevelEnum::class,
            'willing_to_relocate' => 'boolean',
            'is_open_to_remote' => 'boolean',
            'has_disability' => 'boolean',
            'availability_date' => 'date',
            'expected_salary' => 'decimal:2',
            'is_onboarded' => 'boolean',
            'data_consent_given' => 'boolean',
            'onboarding_completed_at' => 'datetime',
            'cv_last_uploaded_at' => 'datetime',
        ];
    }

    /**
     * @return array<string, array{weight: int, fields: array<bool>, label: string}>
     */
    private function getProfileSections(): array
    {
        return [
            'basic_info' => [
                'weight' => 20,
                'label' => 'basic_info',
                'fields' => [
                    filled($this->user->name),
                    filled($this->user->email),
                    $this->phone_number !== null,
                    $this->headline !== null,
                    $this->summary !== null,
                ],
            ],
            'professional' => [
                'weight' => 25,
                'label' => 'professional',
                'fields' => [
                    $this->experience_level !== null,
                    $this->workExperiences()->count() >= 1,
                ],
            ],
            'education' => [
                'weight' => 15,
                'label' => 'education',
                'fields' => [
                    $this->degrees()->count() >= 1,
                ],
            ],
            'skills' => [
                'weight' => 15,
                'label' => 'skills',
                'fields' => [
                    $this->skills()->count() >= 3,
                ],
            ],
            'preferences' => [
                'weight' => 15,
                'label' => 'preferences',
                'fields' => [
                    $this->expected_salary !== null,
                    $this->availability_date !== null,
                    $this->is_open_to_remote || $this->willing_to_relocate,
                ],
            ],
            'links' => [
                'weight' => 10,
                'label' => 'links',
                'fields' => [
                    $this->user->links()->count() >= 2,
                ],
            ],
        ];
    }

    private function calculateTotalExperienceMonths(): int
    {
        return (int) $this->workExperiences
            ->sum(function (WorkExperience $exp) {
                $end = $exp->is_currently_working_here
                    ? now()
                    : ($exp->end_date ?? now());

                return $exp->start_date->diffInMonths($end);
            });
    }

    private function formatExperienceTime(float $totalMonths): string
    {
        $totalMonths = (int) $totalMonths;
        $years = intdiv($totalMonths, 12);
        $months = $totalMonths % 12;

        $yearsPart = $years > 0 ? trans_choice('panel-organization::view.time.year', $years, ['count' => $years]) : '';
        $monthsPart = $months > 0 ? trans_choice('panel-organization::view.time.month', $months,
            ['count' => $months]) : '';

        if ($years > 0 && $months > 0) {
            return $yearsPart.' '.__('panel-organization::view.time.and').' '.$monthsPart;
        }

        if ($years > 0) {
            return $yearsPart;
        }

        return $monthsPart ?: trans_choice('panel-organization::view.time.month', 0, ['count' => 0]);

    }
}
