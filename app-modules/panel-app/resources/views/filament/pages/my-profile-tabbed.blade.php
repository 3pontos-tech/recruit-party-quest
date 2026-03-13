@php
    use He4rt\App\Livewire\MyProfile\CandidateEducation;
    use He4rt\App\Livewire\MyProfile\CandidateLinks;
    use He4rt\App\Livewire\MyProfile\CandidatePreferences;
    use He4rt\App\Livewire\MyProfile\CandidateProfileInfo;
    use He4rt\App\Livewire\MyProfile\CandidateResumeUpload;
    use He4rt\App\Livewire\MyProfile\CandidateSkills;
    use He4rt\App\Livewire\MyProfile\CandidateWorkExperience;
@endphp

<x-filament::page>
    <div x-data="{ activeTab: 'profile' }">
        <x-filament::tabs class="mb-6 w-full justify-center">
            <x-filament::tabs.item
                :alpineActive="'activeTab === \'profile\''"
                tag="button"
                x-on:click="activeTab = 'profile'"
            >
                {{ __('panel-app::filament.profile.tabs.profile') }}
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :alpineActive="'activeTab === \'career\''"
                tag="button"
                x-on:click="activeTab = 'career'"
            >
                {{ __('panel-app::filament.profile.tabs.career') }}
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :alpineActive="'activeTab === \'links\''"
                tag="button"
                x-on:click="activeTab = 'links'"
            >
                {{ __('panel-app::filament.profile.tabs.links') }}
            </x-filament::tabs.item>

            <x-filament::tabs.item
                :alpineActive="'activeTab === \'account\''"
                tag="button"
                x-on:click="activeTab = 'account'"
            >
                {{ __('panel-app::filament.profile.tabs.account') }}
            </x-filament::tabs.item>
        </x-filament::tabs>

        <div
            x-show="activeTab === 'profile'"
            x-cloak
            class="divide-y divide-gray-900/10 dark:divide-white/10 [&>*:not(:first-child)]:pt-6 [&>*:not(:last-child)]:pb-6"
        >
            @livewire(CandidateProfileInfo::class)
            @livewire(CandidatePreferences::class)
        </div>

        <div
            x-show="activeTab === 'career'"
            x-cloak
            class="divide-y divide-gray-900/10 dark:divide-white/10 [&>*:not(:first-child)]:pt-6 [&>*:not(:last-child)]:pb-6"
        >
            @livewire(CandidateResumeUpload::class)
            @livewire(CandidateWorkExperience::class)
            @livewire(CandidateEducation::class)
            @livewire(CandidateSkills::class)
        </div>

        <div
            x-show="activeTab === 'links'"
            x-cloak
            class="divide-y divide-gray-900/10 dark:divide-white/10 [&>*:not(:first-child)]:pt-6 [&>*:not(:last-child)]:pb-6"
        >
            @livewire(CandidateLinks::class)
        </div>

        <div
            x-show="activeTab === 'account'"
            x-cloak
            class="divide-y divide-gray-900/10 dark:divide-white/10 [&>*:not(:first-child)]:pt-6 [&>*:not(:last-child)]:pb-6"
        >
            @livewire('personal_info')
            @livewire('update_password')
            @livewire('browser_sessions')
        </div>
    </div>
</x-filament::page>
