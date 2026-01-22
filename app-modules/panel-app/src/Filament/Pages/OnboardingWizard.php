<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Pages;

use BackedEnum;
use DateTimeZone;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Concerns\EvaluatesClosures;
use Filament\Support\Enums\Width;
use He4rt\App\Filament\Schemas\ResumeFileUpload;
use He4rt\Candidates\Actions\Onboarding\StoreCandidateEducation;
use He4rt\Candidates\Actions\Onboarding\StoreCandidateWorkExperiences;
use He4rt\Candidates\Actions\Onboarding\UpdateCandidateAction;
use He4rt\Candidates\DTOs\CandidateDTO;
use He4rt\Candidates\DTOs\Collections\CandidateEducationCollection;
use He4rt\Candidates\DTOs\Collections\CandidateWorkExperienceCollection;
use He4rt\Users\User;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use Livewire\Attributes\On;

/**
 * @property-read Schema $content
 */
class OnboardingWizard extends Page
{
    use EvaluatesClosures;
    use InteractsWithFormActions;
    use InteractsWithForms;
    use InteractsWithRecord;

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    public ?array $data = [];

    public ?User $user = null;

    public bool $wizardVisible = false;

    public bool $canSkipResumeAnalysis = true;

    protected static ?string $slug = 'onboarding';

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-academic-cap';

    protected static string $layout = 'filament-panels::components.layout.simple';

    protected ?string $heading = '';

    protected Width|string|null $maxContentWidth = Width::ScreenSmall;

    //    protected string $view = 'filament-panels::pages.simple';

    protected static bool $isDiscovered = true;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?int $navigationSort = -1;

    public static function canAccess(): bool
    {
        return true;
    }

    public function mount(): void
    {
        $this->record = auth()->user()->candidate;
        $this->user = auth()->user();
        $this->content->fill();
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('panel-app::pages/onboarding.steps.cv.sections.upload_cv'))
                    ->visible(fn () => ! $this->wizardVisible)
                    ->compact()
                    ->schema([
                        ResumeFileUpload::make('cv_file')->visible(fn () => $this->canSkipResumeAnalysis),
                        Action::make('continue-onboarding')
                            ->disabled(fn () => ! $this->canSkipResumeAnalysis)
                            ->label(__('panel-app::pages/onboarding.actions.continue_without_upload'))
                            ->action(function (): void {
                                $this->wizardVisible = true;
                                $this->maxContentWidth = Width::ScreenExtraLarge;
                            }),
                    ]),
                $this->prepareWizard(),
            ])
            ->record(fn () => $this->record)
            ->statePath('data');
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    public function getSteps(): array
    {
        return [
            $this->getAccountStep(),
            $this->getProfileStep(),
            $this->getPreferencesStep(),
            $this->getReviewStep(),
        ];
    }

    public function handleRegistration(): void
    {
        $data = $this->data;

        if (! ($data['data_consent_given'] ?? false)) {
            Notification::make()
                ->title(__('panel-app::pages/onboarding.notifications.consent_required.title'))->danger()
                ->send();

            return;
        }

        $experiences = CandidateWorkExperienceCollection::fromArray($data['work_experiences']);
        resolve(StoreCandidateWorkExperiences::class)->execute($experiences);

        $education = CandidateEducationCollection::fromArray($data['education']);
        resolve(StoreCandidateEducation::class)->execute($education);

        resolve(UpdateCandidateAction::class)->execute(new CandidateDTO(
            userID: auth()->user()->id,
            phoneNumber: $data['phone'] ?? null,
            headline: $data['headline'] ?? null,
            summary: $data['summary'] ?? null,
            availabilityDate: Date::parse($data['availability_date']),
            willingToRelocate: $data['willing_to_relocate'] ?? false,
            is_open_to_remote: $data['is_open_to_remote'] ?? true,
            expectedSalary: (float) $data['expected_salary'],
            expectedSalaryCurrency: $data['expected_salary_currency'] ?? 'USD',
            linkedin_url: null,
            portfolio_url: null,
            experienceLevel: $data['experience_level'] ?? null,
            contactLinks: null,
            selfIdentifiedGender: null,
            source: null,
            isOnboarded: true,
            onboardingCompletedAt: now(),
            timezone: $data['timezone'] ?? 'UTC',
            preferredLanguage: $data['preferred_language'] ?? 'en',
            dataConsentGiven: true,
        ));

        session()->forget('onboarding_data');

        Notification::make()
            ->title(__('panel-app::pages/onboarding.notifications.onboarding_complete.title'))->success()
            ->send();

        redirect(route('filament.app.pages.app-dashboard'));
    }

    public function getTitle(): string|Htmlable
    {
        return __('panel-app::pages/onboarding.title');
    }

    public function prepareWizard(): Wizard
    {

        $steps = collect($this->getSteps())
            ->map(fn (array $data) => Step::make($data['label'])
                ->schema($data['schema']))
            ->toArray();

        return Wizard::make()
            ->steps($steps)
            ->visible(fn () => $this->wizardVisible)
            ->persistStepInQueryString()
            ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                        <x-filament::button
                            type="submit"
                            wire:click="handleRegistration"
                            size="sm"
                        >
                            {{ __('panel-app::pages/onboarding.actions.finish') }}
                        </x-filament::button>
                    BLADE
            )));
    }

    #[On('echo-private:candidate-onboarding.resume.{user.id},.finished')]
    /**
     * @phpstan-ignore argument.templateType
     * @phpstan-ignore missingType.iterableValue
     */
    public function onResumeAnalyzed(array $payload): void
    {
        $this->canSkipResumeAnalysis = true;
        $fields = $payload['fields'];

        $workState = collect($fields['work_experiences'])->mapWithKeys(fn ($item) => [(string) Str::uuid() => $item])->all();

        $educationState = collect($fields['education'])->mapWithKeys(fn ($item) => [(string) Str::uuid() => $item])->all();

        $this->data['work_experiences'] = $workState;
        $this->data['education'] = $educationState;

        $this->wizardVisible = true;

        Notification::make()
            ->title(__('panel-app::pages/onboarding.steps.cv.fields.cv_file'))
            ->success()
            ->send();
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    protected function getAccountStep(): array
    {
        return [
            'id' => 'account',
            'label' => __('panel-app::pages/onboarding.steps.account.label'),
            'schema' => [
                Section::make(__('panel-app::pages/onboarding.steps.account.sections.account_info'))
                    ->schema([
                        TextInput::make('email')
                            ->label(__('panel-app::pages/onboarding.steps.account.fields.email'))
                            ->readOnly()
                            ->default(fn () => auth()->user()->email),
                        Select::make('timezone')
                            ->label(__('panel-app::pages/onboarding.steps.account.fields.timezone'))
                            ->options(fn () => collect(DateTimeZone::listIdentifiers())
                                ->mapWithKeys(fn ($tz) => [$tz => $tz])
                                ->all())
                            ->searchable()
                            ->required()
                            ->native(false),
                        Select::make('preferred_language')
                            ->label(__('panel-app::pages/onboarding.steps.account.fields.preferred_language'))
                            ->options([
                                'pt_BR' => __('panel-app::pages/onboarding.steps.account.options.preferred_language.pt_BR'),
                                'en_US' => __('panel-app::pages/onboarding.steps.account.options.preferred_language.en_US'),
                            ])
                            ->required()
                            ->default('en'),
                        Toggle::make('data_consent_given')
                            ->label(__('panel-app::pages/onboarding.steps.account.fields.data_consent'))
                            ->accepted(fn ($state) => $state === true)
                            ->helperText(__('panel-app::pages/onboarding.steps.account.fields.data_consent_helper')),
                    ]),
            ],
        ];
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    protected function getProfileStep(): array
    {
        return [
            'id' => 'profile',
            'label' => __('panel-app::pages/onboarding.steps.profile.label'),
            'schema' => [
                Section::make(__('panel-app::pages/onboarding.steps.profile.sections.work_experience'))
                    ->schema([
                        Repeater::make('work_experiences')
                            ->label(__('panel-app::pages/onboarding.steps.profile.fields.work_experience'))
                            ->schema([
                                TextInput::make('company_name')
                                    ->label(__('panel-app::pages/onboarding.steps.profile.fields.company_name'))
                                    ->required(),
                                Textarea::make('description')
                                    ->label(__('panel-app::pages/onboarding.steps.profile.fields.description'))
                                    ->rows(3)
                                    ->required(),
                                DatePicker::make('start_date')
                                    ->label(__('panel-app::pages/onboarding.steps.profile.fields.start_date'))
                                    ->required(),
                                DatePicker::make('end_date')
                                    ->label(__('panel-app::pages/onboarding.steps.profile.fields.end_date'))
                                    ->required(fn (Get $get) => $get('is_currently_working_here') === false),
                                Toggle::make('is_currently_working_here')
                                    ->label(__('panel-app::pages/onboarding.steps.profile.fields.is_currently_working_here')),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['company_name'] ?? null)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('panel-app::pages/onboarding.steps.profile.sections.education'))
                    ->schema([
                        Repeater::make('education')
                            ->label(__('panel-app::pages/onboarding.steps.profile.fields.education'))
                            ->schema([
                                TextInput::make('institution')
                                    ->label(__('panel-app::pages/onboarding.steps.profile.fields.institution'))
                                    ->required(),
                                TextInput::make('degree')
                                    ->label(__('panel-app::pages/onboarding.steps.profile.fields.degree'))
                                    ->required(),
                                TextInput::make('field_of_study')
                                    ->label(__('panel-app::pages/onboarding.steps.profile.fields.field_of_study'))
                                    ->required(),
                                DatePicker::make('start_date')
                                    ->label(__('panel-app::pages/onboarding.steps.profile.fields.start_date'))
                                    ->required(),
                                DatePicker::make('end_date')
                                    ->label(__('panel-app::pages/onboarding.steps.profile.fields.end_date'))
                                    ->required(fn (Get $get) => $get('is_enrolled') === false),
                                Toggle::make('is_enrolled')
                                    ->label(__('panel-app::pages/onboarding.steps.profile.fields.is_enrolled')),
                            ])
                            ->itemLabel(fn (array $state): ?string => $state['institution'] ?? null)
                            ->columnSpanFull(),
                    ]),
                //                Section::make(__('panel-app::pages/onboarding.steps.profile.sections.skills'))
                //                    ->schema([
                //                        TextInput::make('skills_count')
                //                            ->label(__('panel-app::pages/onboarding.steps.profile.fields.skills'))
                //                            ->helperText(__('panel-app::pages/onboarding.steps.profile.fields.skills_helper'))
                //                            ->nullable()
                //                            ->dehydrated(false),
                //                    ]),
            ],
        ];
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    protected function getPreferencesStep(): array
    {
        return [
            'id' => 'preferences',
            'label' => __('panel-app::pages/onboarding.steps.preferences.label'),
            'schema' => [
                Section::make(__('panel-app::pages/onboarding.steps.preferences.sections.compensation'))
                    ->schema([
                        TextInput::make('expected_salary')
                            ->label(__('panel-app::pages/onboarding.steps.preferences.fields.expected_salary'))
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Select::make('expected_salary_currency')
                            ->label(__('panel-app::pages/onboarding.steps.preferences.fields.currency'))
                            ->options(['USD' => 'USD', 'EUR' => 'EUR', 'BRL' => 'BRL'])
                            ->default('USD')
                            ->required(),
                    ]),
                Section::make(__('panel-app::pages/onboarding.steps.preferences.sections.availability'))
                    ->schema([
                        DatePicker::make('availability_date')
                            ->label(__('panel-app::pages/onboarding.steps.preferences.fields.availability_date'))
                            ->required()
                            ->helperText(__('panel-app::pages/onboarding.steps.preferences.fields.availability_date_helper')),
                        Toggle::make('willing_to_relocate')
                            ->label(__('panel-app::pages/onboarding.steps.preferences.fields.willing_to_relocate'))
                            ->default(false),
                        Toggle::make('is_open_to_remote')
                            ->label(__('panel-app::pages/onboarding.steps.preferences.fields.is_open_to_remote'))
                            ->default(true),
                    ]),
                Section::make(__('panel-app::pages/onboarding.steps.preferences.sections.job_interests'))
                    ->schema([
                        Select::make('experience_level')
                            ->label(__('panel-app::pages/onboarding.steps.preferences.fields.experience_level'))
                            ->options(__('panel-app::pages/onboarding.steps.preferences.options.experience_levels'))
                            ->required(),
                        TextInput::make('employment_type_interests')
                            ->label(__('panel-app::pages/onboarding.steps.preferences.fields.employment_type_interests'))
                            ->placeholder(__('panel-app::pages/onboarding.steps.preferences.fields.employment_type_interests_placeholder'))
                            ->helperText(__('panel-app::pages/onboarding.steps.preferences.fields.employment_type_interests_helper')),
                    ]),
            ],
        ];
    }

    /**
     * @phpstan-ignore missingType.iterableValue
     */
    protected function getReviewStep(): array
    {
        return [
            'id' => 'review',
            'label' => __('panel-app::pages/onboarding.steps.review.label'),
            'schema' => [
                Section::make(__('panel-app::pages/onboarding.steps.review.sections.review_summary'))
                    ->schema([
                        //                        TextInput::make('email_review')
                        //                            ->label(__('panel-app::pages/onboarding.steps.review.fields.email'))
                        //                            ->disabled()
                        //                            ->default(auth()->user()->email)
                        //                            ->dehydrated(false),
                        //                        TextInput::make('phone_review')
                        //                            ->label(__('panel-app::pages/onboarding.steps.review.fields.phone'))
                        //                            ->disabled()
                        //                            ->dehydrated(false),
                        //                        TextInput::make('headline_review')
                        //                            ->label(__('panel-app::pages/onboarding.steps.review.fields.headline'))
                        //                            ->disabled()
                        //                            ->dehydrated(false),
                        //                        TextInput::make('salary_review')
                        //                            ->label(__('panel-app::pages/onboarding.steps.review.fields.expected_salary'))
                        //                            ->disabled()
                        //                            ->dehydrated(false)
                        //                            ->prefix('$'),
                        Toggle::make('confirm_submission')
                            ->label(__('panel-app::pages/onboarding.steps.review.fields.confirm_submission'))
                            ->required()
                            ->accepted(fn ($state) => $state === true),
                    ]),
            ],
        ];
    }
}
