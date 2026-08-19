<?php

declare(strict_types=1);

namespace He4rt\Applications\Filament\Exports;

use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use He4rt\Applications\Models\Application;
use He4rt\Candidates\Models\Candidate;
use He4rt\Candidates\Models\Education;
use He4rt\Candidates\Models\Skill;
use He4rt\Candidates\Models\WorkExperience;
use He4rt\Links\Link;
use He4rt\Links\LinkTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Number;
use Override;

class ApplicationExporter extends Exporter
{
    protected static ?string $model = Application::class;

    /**
     * The queue worker boots with APP_LOCALE and has no idea which language the person who
     * clicked "export" was using. Column headings survive the trip because they travel
     * already translated inside the job payload, but enum labels are resolved here, in the
     * worker — without this, a pt_BR user gets Portuguese headings over English values.
     *
     * @param  array<string, string>  $columnMap
     * @param  array<string, mixed>  $options
     */
    public function __construct(Export $export, array $columnMap, array $options)
    {
        parent::__construct($export, $columnMap, $options);

        if (is_string($locale = $options['locale'] ?? null)) {
            App::setLocale($locale);
        }
    }

    /**
     * @return array<ExportColumn>
     */
    public static function getColumns(): array
    {
        return [
            ExportColumn::make('tracking_code')
                ->label(__('applications::filament.export.columns.tracking_code')),
            ExportColumn::make('requisition.post.title')
                ->label(__('applications::filament.export.columns.job_title')),
            ExportColumn::make('candidate.user.name')
                ->label(__('applications::filament.export.columns.candidate_name'))
                ->formatStateUsing(fn (?string $state): ?string => self::escapeFormula($state)),
            ExportColumn::make('candidate.user.email')
                ->label(__('applications::filament.export.columns.candidate_email')),
            ExportColumn::make('candidate.phone_number')
                ->label(__('applications::filament.export.columns.phone_number')),
            ExportColumn::make('status')
                ->label(__('applications::filament.export.columns.status'))
                ->state(fn (Application $record): string => $record->status->getLabel()),
            ExportColumn::make('currentStage.name')
                ->label(__('applications::filament.export.columns.current_stage'))
                ->formatStateUsing(fn (?string $state): ?string => self::escapeFormula($state)),
            ExportColumn::make('source')
                ->label(__('applications::filament.export.columns.source'))
                ->state(fn (Application $record): string => $record->source->getLabel()),
            ExportColumn::make('created_at')
                ->label(__('applications::filament.export.columns.applied_at'))
                ->formatStateUsing(fn (Application $record): string => $record->created_at->format('d/m/Y H:i')),
            ExportColumn::make('candidate.headline')
                ->label(__('applications::filament.export.columns.headline'))
                ->formatStateUsing(fn (?string $state): ?string => self::escapeFormula($state)),
            ExportColumn::make('experience_level')
                ->label(__('applications::filament.export.columns.experience_level'))
                ->state(fn (Application $record): ?string => $record->candidate?->experience_level?->getLabel()),
            ExportColumn::make('total_experience')
                ->label(__('applications::filament.export.columns.total_experience'))
                ->state(fn (Application $record): ?string => mb_trim((string) $record->candidate?->total_experience_formatted) ?: null),
            ExportColumn::make('current_position')
                ->label(__('applications::filament.export.columns.current_position'))
                ->state(fn (Application $record): ?string => self::formatCurrentPosition($record->candidate)),
            ExportColumn::make('location')
                ->label(__('applications::filament.export.columns.location'))
                ->state(fn (Application $record): ?string => self::formatLocation($record->candidate)),
            ExportColumn::make('expected_salary')
                ->label(__('applications::filament.export.columns.expected_salary'))
                ->state(fn (Application $record): ?string => self::formatExpectedSalary($record->candidate)),
            ExportColumn::make('availability_date')
                ->label(__('applications::filament.export.columns.availability_date'))
                ->state(fn (Application $record): ?string => $record->candidate?->availability_date?->format('d/m/Y')),
            ExportColumn::make('work_preference')
                ->label(__('applications::filament.export.columns.work_preference'))
                ->state(fn (Application $record): ?string => self::formatWorkPreference($record->candidate)),
            ExportColumn::make('skills')
                ->label(__('applications::filament.export.columns.skills'))
                ->state(fn (Application $record): ?string => self::formatSkills($record->candidate)),
            ExportColumn::make('education')
                ->label(__('applications::filament.export.columns.education'))
                ->state(fn (Application $record): ?string => self::formatEducation($record->candidate)),
            ExportColumn::make('linkedin_url')
                ->label(__('applications::filament.export.columns.linkedin_url'))
                ->state(fn (Application $record): ?string => self::findLink($record->candidate, LinkTypeEnum::LinkedIn)),
            ExportColumn::make('github_url')
                ->label(__('applications::filament.export.columns.github_url'))
                ->state(fn (Application $record): ?string => self::findLink($record->candidate, LinkTypeEnum::GitHub)),
            ExportColumn::make('candidate.summary')
                ->label(__('applications::filament.export.columns.summary'))
                ->enabledByDefault(false)
                ->formatStateUsing(fn (?string $state): ?string => self::escapeFormula($state)),
            ExportColumn::make('rejected_at')
                ->label(__('applications::filament.export.columns.rejected_at'))
                ->enabledByDefault(false)
                ->state(fn (Application $record): ?string => $record->rejected_at?->format('d/m/Y H:i')),
            ExportColumn::make('rejection_reason_category')
                ->label(__('applications::filament.export.columns.rejection_reason_category'))
                ->enabledByDefault(false)
                ->state(fn (Application $record): ?string => $record->rejection_reason_category?->getLabel()),
            ExportColumn::make('offer_amount')
                ->label(__('applications::filament.export.columns.offer_amount'))
                ->enabledByDefault(false),
            /**
             * Disabled by default on purpose: the accessor issues one query per profile
             * section, so enabling it costs ~6 extra queries per exported row.
             */
            ExportColumn::make('profile_completion')
                ->label(__('applications::filament.export.columns.profile_completion'))
                ->enabledByDefault(false)
                ->state(fn (Application $record): ?int => $record->candidate?->profile_completion_percentage),
        ];
    }

    /**
     * @param  Builder<Application>  $query
     * @return Builder<Application>
     */
    #[Override]
    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with([
            'currentStage',
            'requisition.post',
            'candidate.user.links',
            'candidate.address',
            'candidate.skills',
            'candidate.degrees',
            'candidate.workExperiences',
        ]);
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = __('applications::filament.export.notifications.completed.body', [
            'count' => Number::format($export->successful_rows),
        ]);

        if (($failedRowsCount = $export->getFailedRowsCount()) !== 0) {
            $body .= ' '.__('applications::filament.export.notifications.completed.failed', [
                'count' => Number::format($failedRowsCount),
            ]);
        }

        return $body;
    }

    private static function formatCurrentPosition(?Candidate $candidate): ?string
    {
        $experience = $candidate?->workExperiences
            ->sortByDesc(fn (WorkExperience $experience): string => $experience->start_date->toDateString())
            ->first();

        if (! $experience instanceof WorkExperience) {
            return null;
        }

        return self::escapeFormula(mb_trim(sprintf('%s · %s', $experience->position ?? '-', $experience->company_name)));
    }

    private static function formatLocation(?Candidate $candidate): ?string
    {
        $address = $candidate?->address;

        if ($address === null) {
            return null;
        }

        $parts = array_filter([$address->city, $address->state, $address->country]);

        return $parts === [] ? null : self::escapeFormula(implode(', ', $parts));
    }

    private static function formatExpectedSalary(?Candidate $candidate): ?string
    {
        if ($candidate?->expected_salary === null) {
            return null;
        }

        return sprintf('%s %s', $candidate->expected_salary_currency, number_format((float) $candidate->expected_salary, 2));
    }

    private static function formatWorkPreference(?Candidate $candidate): ?string
    {
        if (! $candidate instanceof Candidate) {
            return null;
        }

        $preferences = array_filter([
            $candidate->is_open_to_remote ? (string) __('applications::filament.export.work_preference.remote') : null,
            $candidate->willing_to_relocate ? (string) __('applications::filament.export.work_preference.relocate') : null,
        ]);

        return $preferences === [] ? null : implode(', ', $preferences);
    }

    private static function formatSkills(?Candidate $candidate): ?string
    {
        $skills = $candidate?->skills
            ->map(fn (Skill $skill): string => $skill->name)
            ->all() ?? [];

        return $skills === [] ? null : self::escapeFormula(implode(', ', $skills));
    }

    private static function formatEducation(?Candidate $candidate): ?string
    {
        $degrees = $candidate?->degrees
            ->map(fn (Education $education): string => mb_trim(sprintf('%s - %s (%s)', $education->degree, $education->field_of_study, $education->institution)))
            ->all() ?? [];

        return $degrees === [] ? null : self::escapeFormula(implode(' | ', $degrees));
    }

    private static function findLink(?Candidate $candidate, LinkTypeEnum $type): ?string
    {
        return $candidate?->user->links
            ->first(fn (Link $link): bool => $link->type === $type)
            ?->url;
    }

    /**
     * Spreadsheet software evaluates cells starting with `=`, `+`, `-` or `@` as
     * formulas. Candidate-provided text is untrusted, so it gets a leading quote.
     */
    private static function escapeFormula(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return in_array($value[0], ['=', '+', '-', '@'], true) ? "'".$value : $value;
    }
}
