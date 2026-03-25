<?php

declare(strict_types=1);

namespace He4rt\PluginSeo;

class Metadata
{
    public function __construct(
        protected ?string $title = null,
        protected ?string $description = null,
        protected ?string $url = null,
        protected ?string $canonical = null,
        protected ?string $robots = null,
        protected ?string $ogType = 'website',
        protected ?string $ogImage = null,
        protected ?int $ogImageWidth = 1200,
        protected ?int $ogImageHeight = 630,
        protected ?string $twitterCard = 'summary_large_image',
        protected ?string $twitterSite = null,
        protected ?string $locale = null,
        protected ?array $jsonLd = null,
    ) {}

    public static function make(): static
    {
        return new static();
    }

    /**
     * Create a Metadata instance from the filament-seo config file.
     * Used as the lowest-priority fallback in the merge chain.
     */
    public static function fromConfig(): static
    {
        return new static(
            title: config('filament-seo.title'),
            description: config('filament-seo.description'),
            robots: config('filament-seo.robots'),
            ogType: config('filament-seo.og_type', 'website'),
            ogImage: config('filament-seo.og_image'),
            ogImageWidth: config('filament-seo.og_image_width', 1200),
            ogImageHeight: config('filament-seo.og_image_height', 630),
            twitterCard: config('filament-seo.twitter_card', 'summary_large_image'),
            twitterSite: config('filament-seo.twitter_site'),
        );
    }

    public function title(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function url(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function canonical(string $canonical): static
    {
        $this->canonical = $canonical;

        return $this;
    }

    public function robots(string $robots): static
    {
        $this->robots = $robots;

        return $this;
    }

    public function ogType(string $ogType): static
    {
        $this->ogType = $ogType;

        return $this;
    }

    public function ogImage(string $ogImage, ?int $width = null, ?int $height = null): static
    {
        $this->ogImage = $ogImage;

        if ($width !== null) {
            $this->ogImageWidth = $width;
        }

        if ($height !== null) {
            $this->ogImageHeight = $height;
        }

        return $this;
    }

    public function twitterCard(string $twitterCard): static
    {
        $this->twitterCard = $twitterCard;

        return $this;
    }

    public function twitterSite(string $twitterSite): static
    {
        $this->twitterSite = $twitterSite;

        return $this;
    }

    public function locale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function jsonLd(array $jsonLd): static
    {
        $this->jsonLd = $jsonLd;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getCanonical(): ?string
    {
        return $this->canonical;
    }

    public function getRobots(): ?string
    {
        return $this->robots;
    }

    public function getOgType(): ?string
    {
        return $this->ogType;
    }

    public function getOgImage(): ?string
    {
        return $this->ogImage;
    }

    public function getOgImageWidth(): ?int
    {
        return $this->ogImageWidth;
    }

    public function getOgImageHeight(): ?int
    {
        return $this->ogImageHeight;
    }

    public function getTwitterCard(): ?string
    {
        return $this->twitterCard;
    }

    public function getTwitterSite(): ?string
    {
        return $this->twitterSite;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getJsonLd(): ?array
    {
        return $this->jsonLd;
    }

    /**
     * Merge with defaults — this instance's values take priority,
     * defaults fill in nulls.
     */
    public function mergeWith(self $defaults): static
    {
        $merged = clone $this;
        $merged->title ??= $defaults->title;
        $merged->description ??= $defaults->description;
        $merged->url ??= $defaults->url;
        $merged->canonical ??= $defaults->canonical;
        $merged->robots ??= $defaults->robots;
        $merged->ogType ??= $defaults->ogType;
        $merged->ogImage ??= $defaults->ogImage;
        $merged->ogImageWidth ??= $defaults->ogImageWidth;
        $merged->ogImageHeight ??= $defaults->ogImageHeight;
        $merged->twitterCard ??= $defaults->twitterCard;
        $merged->twitterSite ??= $defaults->twitterSite;
        $merged->locale ??= $defaults->locale;
        $merged->jsonLd ??= $defaults->jsonLd;

        return $merged;
    }

    /**
     * Convert to array for passing to Blade view.
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'url' => $this->url ?? request()->url(),
            'canonical' => $this->canonical ?? $this->url ?? request()->url(),
            'robots' => $this->robots,
            'ogType' => $this->ogType,
            'ogImage' => $this->ogImage,
            'ogImageWidth' => $this->ogImageWidth,
            'ogImageHeight' => $this->ogImageHeight,
            'twitterCard' => $this->twitterCard,
            'twitterSite' => $this->twitterSite,
            'locale' => $this->locale ?? app()->getLocale(),
            'jsonLd' => $this->jsonLd,
            'siteName' => config('app.name'),
        ];
    }
}
