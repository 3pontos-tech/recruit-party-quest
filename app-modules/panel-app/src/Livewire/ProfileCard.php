<?php

declare(strict_types=1);

namespace He4rt\App\Livewire;

use He4rt\Links\Link;
use He4rt\Links\LinkTypeEnum;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class ProfileCard extends Component
{
    public function render(): Factory|View
    {
        $user = auth()->user();
        $links = $user->links;
        $candidate = $user->candidate;

        /** @var Collection<string, Collection<int, Link>> $groupedLinks */
        $groupedLinks = $links->groupBy(fn ($link) => $link->type->value);

        return view('panel-app::livewire.profile-card', [
            'contactLinks' => $this->getContactLinks($groupedLinks),
            'socialLinks' => $this->getSocialLinks($groupedLinks),
            'candidate' => $candidate,
            'profileCompletionPercentage' => $candidate->profile_completion_percentage ?? 0,
            'missingSections' => $candidate ? $candidate->getMissingProfileSections() : [],
        ]);
    }

    /**
     * Returns contact-type links (Email, Phone).
     *
     * @param  Collection<string, Collection<int, Link>>  $groupedLinks
     * @return Collection<int, Link>
     */
    private function getContactLinks(Collection $groupedLinks): Collection
    {
        return collect([
            LinkTypeEnum::Email->value,
            LinkTypeEnum::Phone->value,
        ])->flatMap(fn (string $type) => $groupedLinks->get($type, collect()));
    }

    /**
     * Returns social/external links (Social, Website, External).
     *
     * @param  Collection<string, Collection<int, Link>>  $groupedLinks
     * @return Collection<int, Link>
     */
    private function getSocialLinks(Collection $groupedLinks): Collection
    {
        return collect([
            LinkTypeEnum::Social->value,
            LinkTypeEnum::Website->value,
            LinkTypeEnum::External->value,
        ])->flatMap(fn (string $type) => $groupedLinks->get($type, collect()));
    }
}
