# Filament SEO Plugin

A Filament v5 plugin for declarative SEO metadata management. Define page metadata using a fluent PHP API — the plugin handles rendering into `<head>` automatically.

## Features

- Fluent `Metadata` builder with full SEO support (title, description, canonical, robots, Open Graph, Twitter Cards, JSON-LD)
- Filament Plugin architecture — register on any panel with `SeoPlugin::make()`
- Per-panel default metadata via `->defaults()`
- Per-page/per-record metadata via `HasMetadata` interface
- Config-driven global defaults (`filament-seo.php`)
- Non-production safety: automatically sets `noindex, nofollow` when `app()->isProduction()` returns `false`
- Three-level merge chain with clear priority: **Page > Panel Plugin > Config**

## Requirements

- PHP 8.2+
- Laravel 11+
- Filament v5+

## Installation

### As a Composer package

```bash
composer require he4rt/plugin-seo
```

### As a local module (path repository)

Add to your root `composer.json` repositories:

```json
{
    "type": "path",
    "url": "app-modules/*",
    "options": { "symlink": true }
}
```

Then require it:

```json
"require": {
    "he4rt/plugin-seo": ">=1"
}
```

## Setup

### 1. Register the plugin on your panel

```php
use He4rt\PluginSeo\Metadata;
use He4rt\PluginSeo\SeoPlugin;

public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            SeoPlugin::make()
                ->defaults(
                    Metadata::make()
                        ->ogImage(asset('images/seo.png'))
                        ->description('Your default site description.')
                ),
        ]);
}
```

For admin panels that shouldn't be indexed:

```php
SeoPlugin::make()
    ->defaults(
        Metadata::make()
            ->robots('noindex, nofollow')
    ),
```

### 2. Publish the config (optional)

```bash
php artisan vendor:publish --tag=filament-seo-config
```

This creates `config/filament-seo.php` with global defaults.

## Usage

### Adding metadata to a page

Implement `HasMetadata` and use the `InteractsWithMetadata` trait:

```php
use He4rt\PluginSeo\Concerns\InteractsWithMetadata;
use He4rt\PluginSeo\Contracts\HasMetadata;
use He4rt\PluginSeo\Metadata;

class LandingPage extends Page implements HasMetadata
{
    use InteractsWithMetadata;

    public function getMetadata(): Metadata
    {
        return Metadata::make()
            ->title('Welcome to My App')
            ->description('A short description of this page.')
            ->ogImage(asset('images/og-cover.png'));
    }
}
```

### Per-record metadata (Resource pages)

Access `$this->record` inside `getMetadata()` for dynamic metadata:

```php
class ViewJobPosting extends ViewRecord implements HasMetadata
{
    use InteractsWithMetadata;

    public function getMetadata(): Metadata
    {
        return Metadata::make()
            ->title($this->record->title . ' - ' . config('app.name'))
            ->description($this->record->summary)
            ->ogImage($this->record->cover_image ?? asset('images/default-og.png'))
            ->jsonLd([
                '@context' => 'https://schema.org',
                '@type' => 'JobPosting',
                'title' => $this->record->title,
                'description' => $this->record->summary,
                'datePosted' => $this->record->created_at->toIso8601String(),
                'hiringOrganization' => [
                    '@type' => 'Organization',
                    'name' => $this->record->company->name,
                ],
            ]);
    }
}
```

## Metadata API

All methods return `$this` for chaining:

| Method                        | Description                                          |
| ----------------------------- | ---------------------------------------------------- |
| `title(string)`               | Page title and `og:title`                            |
| `description(string)`         | Meta description and `og:description`                |
| `url(string)`                 | Page URL (defaults to `request()->url()`)            |
| `canonical(string)`           | Canonical URL (defaults to `url`)                    |
| `robots(string)`              | Robots directive (e.g., `'index, follow'`)           |
| `ogType(string)`              | Open Graph type (default: `'website'`)               |
| `ogImage(string, ?int, ?int)` | OG image URL with optional width/height              |
| `twitterCard(string)`         | Twitter card type (default: `'summary_large_image'`) |
| `twitterSite(string)`         | Twitter `@handle` for the site                       |
| `locale(string)`              | Page locale (defaults to `app()->getLocale()`)       |
| `jsonLd(array)`               | JSON-LD structured data (any schema.org type)        |

## Merge Priority

When metadata is resolved, values follow this priority (highest wins):

```
1. Page getMetadata()        — per-page or per-record values
2. SeoPlugin->defaults()     — per-panel defaults
3. config('filament-seo.*')  — global config file
4. Constructor defaults      — hardcoded fallbacks (og_type, twitter_card, etc.)
```

Non-production environments **always** override `robots` to `noindex, nofollow`, regardless of any level above.

## Config Reference

```php
// config/filament-seo.php
return [
    'title'         => null,
    'description'   => null,
    'robots'        => 'index, follow',

    'og_type'       => 'website',
    'og_image'      => null,
    'og_image_width'  => 1200,
    'og_image_height' => 630,

    'twitter_card'  => 'summary_large_image',
    'twitter_site'  => null,
];
```

## What gets rendered

The plugin injects the following into `<head>` via Filament's `PanelsRenderHook::HEAD_START`:

- `<title>` and `<meta name="title">`
- `<meta name="description">`
- `<meta name="robots">`
- `<link rel="canonical">`
- Open Graph tags (`og:type`, `og:url`, `og:title`, `og:description`, `og:image`, `og:site_name`, `og:locale`)
- Twitter Card tags (`twitter:card`, `twitter:url`, `twitter:title`, `twitter:description`, `twitter:image`, `twitter:site`)
- iMessage/WhatsApp compatibility tags
- Schema.org microdata (`itemprop`)
- JSON-LD `<script>` block (when provided)

## Testing

```bash
php artisan test app-modules/plugin-seo/tests
```

## License

Proprietary. See [LICENSE](LICENSE) for details.
