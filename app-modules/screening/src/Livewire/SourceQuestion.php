<?php

declare(strict_types=1);

namespace He4rt\Screening\Livewire;

use He4rt\Applications\Enums\CandidateSourceEnum;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Modelable;
use Livewire\Component;

class SourceQuestion extends Component
{
    #[Modelable]
    public CandidateSourceEnum|string $value;

    public function render(): View
    {
        return view('screening::livewire.source-question');
    }
}
