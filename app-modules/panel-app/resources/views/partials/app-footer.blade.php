@use(He4rt\Term\Actions\GetActiveTerms)
@use(He4rt\Term\Filament\Pages\TermPage)

@php
    $terms = resolve(GetActiveTerms::class)
        ->execute()
        ->map(
            fn ($term): array => [
                'title' => $term->title,
                'url' => TermPage::getUrl(['slug' => $term->slug]),
            ],
        )
        ->all();
@endphp

<x-he4rt::partials.footer
    :terms="$terms"
    logoPath="images/3pontos/logo.svg"
    logoSize="sm"
    description="Somos o ecossistema que une solução e conhecimento em um único lugar. Aceleramos sua empresa. Fortalecemos sua carreira."
    company="3 Pontos"
    :columns="[
        'Navegação' => [
            'Home' => '#',
            'Missão social' => '#social-action',
            'Comunidade' => '#community',
            'Propósito' => '#meet-up',
            'Palestrantes' => '#speakers',
            'Lineup' => '#lineup',
            'Ao vivo' => '#watch-live',
            'Parceiros' => '#partners',
            'Saiba mais' => '#about',
        ]
    ]"
/>
