@if ($title)
    <title>{{ $title }}</title>
    <meta name="title" content="{{ $title }}" />
@endif

@if ($description)
    <meta name="description" content="{{ $description }}" />
@endif

@if ($robots)
    <meta name="robots" content="{{ $robots }}" />
@endif

<link rel="canonical" href="{{ $canonical }}" />
<meta http-equiv="X-UA-Compatible" content="IE=edge" />

{{-- Open Graph / Facebook / LinkedIn / Slack / Discord --}}
<meta property="og:type" content="{{ $ogType ?? 'website' }}" />
<meta property="og:url" content="{{ $url }}" />
@if ($title)
    <meta property="og:title" content="{{ $title }}" />
@endif

@if ($description)
    <meta property="og:description" content="{{ $description }}" />
@endif

@if ($ogImage)
    <meta property="og:image" content="{{ $ogImage }}" />
    <meta property="og:image:secure_url" content="{{ $ogImage }}" />
    <meta property="og:image:type" content="image/png" />
    <meta property="og:image:alt" content="{{ $title }}" />
    <meta property="og:image:width" content="{{ $ogImageWidth ?? 1200 }}" />
    <meta property="og:image:height" content="{{ $ogImageHeight ?? 630 }}" />
@endif

<meta property="og:site_name" content="{{ $siteName }}" />
@if ($locale)
    <meta property="og:locale" content="{{ str_replace('-', '_', $locale) }}" />
@endif

{{-- Twitter --}}
<meta name="twitter:card" content="{{ $twitterCard ?? 'summary_large_image' }}" />
<meta name="twitter:url" content="{{ $url }}" />
@if ($title)
    <meta name="twitter:title" content="{{ $title }}" />
@endif

@if ($description)
    <meta name="twitter:description" content="{{ $description }}" />
@endif

@if ($ogImage)
    <meta name="twitter:image" content="{{ $ogImage }}" />
@endif

@if ($twitterSite)
    <meta name="twitter:site" content="{{ $twitterSite }}" />
@endif

{{-- iMessage / WhatsApp compatibility --}}
@if ($ogImage)
    <meta name="image" content="{{ $ogImage }}" />
    <meta itemprop="image" content="{{ $ogImage }}" />
@endif

{{-- Schema.org microdata --}}
@if ($title)
    <meta itemprop="name" content="{{ $title }}" />
@endif

@if ($description)
    <meta itemprop="description" content="{{ $description }}" />
@endif

{{-- JSON-LD Structured Data --}}
@if ($jsonLd)
    <script type="application/ld+json">
        {!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>
@endif
