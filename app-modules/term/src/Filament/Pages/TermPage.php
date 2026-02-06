<?php

declare(strict_types=1);

namespace He4rt\Term\Filament\Pages;

use Filament\Pages\Page;
use He4rt\Term\Models\Term;
use Illuminate\Contracts\Support\Htmlable;

class TermPage extends Page
{
    public ?Term $term = null;

    /**
     * @var array<int, array{id: string, title: string, body: string, show_in_sidebar: bool}>
     */
    public array $sections = [];

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'terms/view/{slug}';

    protected string $view = 'term::filament.pages.term-page';

    public function mount(string $slug): void
    {
        $this->term = Term::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $this->sections = $this->term->content ?? [];
    }

    public function getTitle(): string|Htmlable
    {
        return $this->term->title ?? __('term::filament.page.title');
    }

    /**
     * @return array<int, array{id: string, title: string}>
     */
    public function getSidebarSections(): array
    {
        return collect($this->sections)
            ->filter(fn (array $section): bool => $section['show_in_sidebar'])
            ->map(fn (array $section): array => [
                'id' => $section['id'],
                'title' => $section['title'],
            ])
            ->values()
            ->all();
    }
}
