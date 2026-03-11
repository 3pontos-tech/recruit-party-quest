<?php

declare(strict_types=1);

use He4rt\Links\LinkTypeEnum;

it('every case has a non-empty label', function (): void {
    foreach (LinkTypeEnum::cases() as $case) {
        expect($case->label())->not->toBeEmpty();
    }
});

it('every case has a non-empty icon', function (): void {
    foreach (LinkTypeEnum::cases() as $case) {
        expect($case->icon())->not->toBeEmpty();
    }
});

it('every case has a non-empty url placeholder', function (): void {
    foreach (LinkTypeEnum::cases() as $case) {
        expect($case->urlPlaceholder())->not->toBeEmpty()->toStartWith('https://');
    }
});

it('getLabel returns the same as label', function (): void {
    foreach (LinkTypeEnum::cases() as $case) {
        expect($case->getLabel())->toBe($case->label());
    }
});

it('has expected platform cases', function (): void {
    $values = array_map(fn (LinkTypeEnum $case) => $case->value, LinkTypeEnum::cases());

    expect($values)->toContain('linkedin', 'github', 'instagram', 'twitter', 'youtube', 'behance', 'dribbble', 'website', 'other');
});

it('other case uses generic link icon', function (): void {
    expect(LinkTypeEnum::Other->icon())->toBe('heroicon-o-link');
});

it('website case uses globe icon', function (): void {
    expect(LinkTypeEnum::Website->icon())->toBe('heroicon-o-globe-alt');
});

it('platform cases have allowed domains', function (): void {
    $restricted = [
        LinkTypeEnum::LinkedIn,
        LinkTypeEnum::GitHub,
        LinkTypeEnum::Instagram,
        LinkTypeEnum::Twitter,
        LinkTypeEnum::YouTube,
        LinkTypeEnum::Behance,
        LinkTypeEnum::Dribbble,
    ];

    foreach ($restricted as $case) {
        expect($case->allowedDomains())->toBeArray()->not->toBeEmpty();
    }
});

it('website and other have no domain restriction', function (): void {
    expect(LinkTypeEnum::Website->allowedDomains())->toBeNull();
    expect(LinkTypeEnum::Other->allowedDomains())->toBeNull();
});

it('twitter allows both x.com and twitter.com', function (): void {
    expect(LinkTypeEnum::Twitter->allowedDomains())->toContain('x.com', 'twitter.com');
});
