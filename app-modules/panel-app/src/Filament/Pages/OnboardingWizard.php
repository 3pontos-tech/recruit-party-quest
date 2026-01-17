<?php

declare(strict_types=1);

namespace He4rt\App\Filament\Pages;

use BackedEnum;
use DateTimeZone;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithRecord;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Concerns\EvaluatesClosures;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

/**
 * @property-read Schema $form
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

    protected static ?string $slug = 'onboarding';

    protected static string|null|BackedEnum $navigationIcon = 'heroicon-o-academic-cap';

    protected string $view = 'panel-app::filament.app.pages.onboarding';

    protected static bool $isDiscovered = true;

    protected static ?int $navigationSort = -1;

    public static function canAccess(): bool
    {
        $candidate = auth()->user()?->candidate;

        return $candidate !== null && ! $candidate->is_onboarded;
    }

    public function mount(): void
    {
        $this->record = auth()->user()->candidate;
        $this->form->fill();
    }

    public function saveProgress(): void
    {
        $data = $this->data;

        unset($data['cv_file']);
        session(['onboarding_data' => $data]);

        Notification::make()
            ->title(__('panel-app::pages/onboarding.notifications.progress_saved.title'))->success()
            ->send();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components(function (): Wizard {
                $steps = collect($this->getSteps())
                    ->map(fn (array $data) => Step::make($data['label'])
                        ->schema($data['schema']))
                    ->toArray();

                return Wizard::make()
                    ->steps($steps)
                    ->persistStepInQueryString()
                    ->submitAction(new HtmlString(Blade::render(<<<'BLADE'
                        <x-filament::button
                            type="submit"
                            wire:click="handleRegistration"
                            size="sm"
                        >
                            Finalizar
                        </x-filament::button>
                    BLADE
                    )));
            })
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
            $this->getCvStep(),
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

        if (count($data['work_experiences'] ?? []) < 1) {
            Notification::make()
                ->title(__('panel-app::pages/onboarding.notifications.work_experience_required.title'))->danger()
                ->send();

            return;
        }

        if (count($data['education'] ?? []) < 1) {
            Notification::make()
                ->title(__('panel-app::pages/onboarding.notifications.education_required.title'))->danger()
                ->send();

            return;
        }

        auth()->user()->candidate()->update([
            'phone_number' => $data['phone'] ?? null,
            'headline' => $data['headline'] ?? null,
            'summary' => $data['summary'] ?? null,
            'expected_salary' => $data['expected_salary'] ?? null,
            'expected_salary_currency' => $data['expected_salary_currency'] ?? 'USD',
            'availability_date' => $data['availability_date'] ?? null,
            'willing_to_relocate' => $data['willing_to_relocate'] ?? false,
            'is_open_to_remote' => $data['is_open_to_remote'] ?? true,
            'experience_level' => $data['experience_level'] ?? null,
            'timezone' => $data['timezone'] ?? 'UTC',
            'preferred_language' => $data['preferred_language'] ?? 'en',
            'data_consent_given' => true,
            'is_onboarded' => true,
            'onboarding_completed_at' => now(),
        ]);

        session()->forget('onboarding_data');

        Notification::make()
            ->title(__('panel-app::pages/onboarding.notifications.onboarding_complete.title'))->success()
            ->send();

        redirect(route('filament.app.pages.dashboard'));
    }

    public function getTitle(): string|Htmlable
    {
        return __('panel-app::pages/onboarding.title');
    }

    /**
     * @return Action[]
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save_progress')
                ->label(__('panel-app::pages/onboarding.actions.save_progress'))
                ->action('saveProgress'),
        ];
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
                                'pt_BR' => 'Português (Brasil)',
                                'en_US' => 'English (United States)',
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
    protected function getCvStep(): array
    {
        return [
            'id' => 'cv',
            'label' => __('panel-app::pages/onboarding.steps.cv.label'),
            'description' => __('panel-app::pages/onboarding.steps.cv.description'),
            'schema' => [
                Section::make(__('panel-app::pages/onboarding.steps.cv.sections.upload_cv'))
                    ->schema([
                        FileUpload::make('cv_file')
                            ->label(__('panel-app::pages/onboarding.steps.cv.fields.cv_file'))
                            ->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'])
                            ->maxSize(10240)
                            ->directory('cv-uploads')
                            ->visibility('private')
                            ->required()
                            ->helperText(__('panel-app::pages/onboarding.steps.cv.fields.cv_file_helper')),
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
                                    ->required(),
                                Toggle::make('is_currently_working_here')
                                    ->label(__('panel-app::pages/onboarding.steps.profile.fields.is_currently_working_here')),
                            ])
                            ->minItems(1)
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
                                    ->required(),
                                Toggle::make('is_enrolled')
                                    ->label(__('panel-app::pages/onboarding.steps.profile.fields.is_enrolled')),
                            ])
                            ->minItems(1)
                            ->itemLabel(fn (array $state): ?string => $state['institution'] ?? null)
                            ->columnSpanFull(),
                    ]),
                Section::make(__('panel-app::pages/onboarding.steps.profile.sections.skills'))
                    ->schema([
                        TextInput::make('skills_count')
                            ->label(__('panel-app::pages/onboarding.steps.profile.fields.skills'))
                            ->helperText(__('panel-app::pages/onboarding.steps.profile.fields.skills_helper'))
                            ->disabled()
                            ->dehydrated(false),
                    ]),
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
                            ->placeholder('full_time_employee, contractor, intern')
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
                        TextInput::make('email_review')
                            ->label(__('panel-app::pages/onboarding.steps.review.fields.email'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('phone_review')
                            ->label(__('panel-app::pages/onboarding.steps.review.fields.phone'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('headline_review')
                            ->label(__('panel-app::pages/onboarding.steps.review.fields.headline'))
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('salary_review')
                            ->label(__('panel-app::pages/onboarding.steps.review.fields.expected_salary'))
                            ->disabled()
                            ->dehydrated(false)
                            ->prefix('$'),
                        Toggle::make('confirm_submission')
                            ->label(__('panel-app::pages/onboarding.steps.review.fields.confirm_submission'))
                            ->required()
                            ->accepted(fn ($state) => $state === true),
                    ]),
            ],
        ];
    }
}
