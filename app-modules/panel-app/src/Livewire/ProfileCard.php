<?php

declare(strict_types=1);

namespace He4rt\App\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ProfileCard extends Component
{
    public function render(): Factory|View
    {
        $user = auth()->user();
        $links = $user->links;
        $candidate = $user->candidate;

        return view('panel-app::livewire.profile-card', [
            'links' => $links,
            'candidate' => $candidate,
            'profileCompletionPercentage' => $candidate->profile_completion_percentage ?? 0,
            'missingSections' => $candidate ? $candidate->getMissingProfileSections() : [],
        ]);
    }
}
