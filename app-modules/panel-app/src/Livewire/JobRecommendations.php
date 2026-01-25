<?php

declare(strict_types=1);

namespace He4rt\App\Livewire;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class JobRecommendations extends Component
{
    public function render(): Factory|View
    {
        return view('panel-app::livewire.job-recommendations');
    }
}
