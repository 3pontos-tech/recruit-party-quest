<?php

declare(strict_types=1);

namespace He4rt\App;

use He4rt\App\Livewire\JobRecommendations;
use He4rt\App\Livewire\ProfileCard;
use He4rt\App\Livewire\MyProfile\CandidateEducation;
use He4rt\App\Livewire\MyProfile\CandidatePreferences;
use He4rt\App\Livewire\MyProfile\CandidateProfileInfo;
use He4rt\App\Livewire\MyProfile\CandidateSkills;
use He4rt\App\Livewire\MyProfile\CandidateWorkExperience;
use He4rt\App\Livewire\ResumeFileUploadProgress;
use He4rt\App\Livewire\SearchJobs;
use He4rt\App\Livewire\UserLatestApplications;
use He4rt\App\Livewire\UserProfileOverview;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class PanelAppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Livewire::component('user-latest-applications', UserLatestApplications::class);
        Livewire::component('resume-file-upload-progress', ResumeFileUploadProgress::class);
        Livewire::component('search-jobs', SearchJobs::class);
        Livewire::component('job-recommendations', JobRecommendations::class);
        Livewire::component('profile-card', ProfileCard::class);
        Livewire::component('user-profile-overview', UserProfileOverview::class);
        Livewire::component('candidate_profile_info', CandidateProfileInfo::class);
        Livewire::component('candidate_preferences', CandidatePreferences::class);
        Livewire::component('candidate_education', CandidateEducation::class);
        Livewire::component('candidate_work_experience', CandidateWorkExperience::class);
        Livewire::component('candidate_skills', CandidateSkills::class);

        $this->loadTranslationsFrom(__DIR__.'/../lang', 'panel-app');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'panel-app');
    }
}
