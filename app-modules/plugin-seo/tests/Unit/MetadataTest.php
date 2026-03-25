<?php

declare(strict_types=1);

use He4rt\PluginSeo\Metadata;
use He4rt\PluginSeo\SeoPlugin;

it('creates metadata with fluent builder', function (): void {
    $metadata = Metadata::make()
        ->title('Test Page')
        ->description('A test description')
        ->url('https://example.com')
        ->robots('index, follow');

    expect($metadata->getTitle())->toBe('Test Page')
        ->and($metadata->getDescription())->toBe('A test description')
        ->and($metadata->getUrl())->toBe('https://example.com')
        ->and($metadata->getRobots())->toBe('index, follow');
});

it('has sensible constructor defaults', function (): void {
    $metadata = Metadata::make();

    expect($metadata->getOgType())->toBe('website')
        ->and($metadata->getTwitterCard())->toBe('summary_large_image')
        ->and($metadata->getOgImageWidth())->toBe(1200)
        ->and($metadata->getOgImageHeight())->toBe(630)
        ->and($metadata->getTitle())->toBeNull()
        ->and($metadata->getDescription())->toBeNull();
});

it('merges metadata with defaults, page values take priority', function (): void {
    $page = Metadata::make()->title('My Page');
    $defaults = Metadata::make()
        ->title('Default Title')
        ->description('Default description')
        ->robots('index');

    $merged = $page->mergeWith($defaults);

    expect($merged->getTitle())->toBe('My Page')
        ->and($merged->getDescription())->toBe('Default description')
        ->and($merged->getRobots())->toBe('index');
});

it('does not mutate original metadata when merging', function (): void {
    $page = Metadata::make()->title('Original');
    $defaults = Metadata::make()->description('Added via merge');

    $merged = $page->mergeWith($defaults);

    expect($page->getDescription())->toBeNull()
        ->and($merged->getDescription())->toBe('Added via merge');
});

it('sets og image with optional dimensions', function (): void {
    $metadata = Metadata::make()->ogImage('https://example.com/image.png', 800, 400);

    expect($metadata->getOgImage())->toBe('https://example.com/image.png')
        ->and($metadata->getOgImageWidth())->toBe(800)
        ->and($metadata->getOgImageHeight())->toBe(400);
});

it('keeps default dimensions when ogImage is called without them', function (): void {
    $metadata = Metadata::make()->ogImage('https://example.com/image.png');

    expect($metadata->getOgImageWidth())->toBe(1200)
        ->and($metadata->getOgImageHeight())->toBe(630);
});

it('converts to array with computed defaults', function (): void {
    $array = Metadata::make()
        ->title('Test')
        ->description('A description')
        ->toArray();

    expect($array['title'])->toBe('Test')
        ->and($array['description'])->toBe('A description')
        ->and($array['siteName'])->toBe(config('app.name'))
        ->and($array['ogType'])->toBe('website')
        ->and($array['twitterCard'])->toBe('summary_large_image')
        ->and($array['locale'])->toBe(app()->getLocale());
});

it('builds metadata from config file', function (): void {
    $metadata = Metadata::fromConfig();

    expect($metadata->getTitle())->toBe(config('filament-seo.title'))
        ->and($metadata->getDescription())->toBe(config('filament-seo.description'))
        ->and($metadata->getRobots())->toBe(config('filament-seo.robots'))
        ->and($metadata->getOgType())->toBe(config('filament-seo.og_type'));
});

it('supports json-ld structured data', function (): void {
    $jsonLd = [
        '@context' => 'https://schema.org',
        '@type' => 'JobPosting',
        'title' => 'Senior Developer',
    ];

    $metadata = Metadata::make()->jsonLd($jsonLd);

    expect($metadata->getJsonLd())->toBe($jsonLd)
        ->and($metadata->toArray()['jsonLd'])->toBe($jsonLd);
});

it('registers seo plugin with defaults', function (): void {
    $plugin = SeoPlugin::make()->defaults(
        Metadata::make()->robots('noindex, nofollow')
    );

    expect($plugin->getId())->toBe('seo')
        ->and($plugin->getDefaults()->getRobots())->toBe('noindex, nofollow');
});

it('supports full merge chain: page → plugin → config', function (): void {
    $page = Metadata::make()->title('Page Title');
    $plugin = Metadata::make()->description('Plugin description')->robots('noindex');
    $config = Metadata::make()
        ->title('Config Title')
        ->description('Config description')
        ->ogImage('https://example.com/default.png');

    $merged = $page->mergeWith($plugin)->mergeWith($config);

    expect($merged->getTitle())->toBe('Page Title')
        ->and($merged->getDescription())->toBe('Plugin description')
        ->and($merged->getRobots())->toBe('noindex')
        ->and($merged->getOgImage())->toBe('https://example.com/default.png');
});
