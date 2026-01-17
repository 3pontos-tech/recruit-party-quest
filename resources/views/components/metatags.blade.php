@props([
    'description',
    'url',
    'title',
    'coverImage',
])

<meta http-equiv="X-UA-Compatible" content="IE=edge" />

<meta name="title" content="{{ $title }}" />
<meta name="description" content="{{ $description }}" />
<link rel="canonical" href="{{ $url }}" />

<!-- Open Graph / Facebook / LinkedIn / Slack / Discord -->
<meta property="og:type" content="website" />
<meta property="og:url" content="{{ $url }}" />
<meta property="og:title" content="{{ $title }}" />
<meta property="og:description" content="{{ $description }}" />
<meta property="og:image" content="{{ $coverImage }}" />
<meta property="og:image:secure_url" content="{{ $coverImage }}" />
<meta property="og:image:type" content="image/png" />
<meta property="og:image:alt" content="{{ $title }}" />
<meta property="og:site_name" content="{{ config('app.name') }}" />

<!-- Optional OG image sizes (helps Slack/Discord) -->
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:url" content="{{ $url }}" />
<meta name="twitter:title" content="{{ $title }}" />
<meta name="twitter:description" content="{{ $description }}" />
<meta name="twitter:image" content="{{ $coverImage }}" />

<!-- iMessage / WhatsApp compatibility -->
<meta name="image" content="{{ $coverImage }}" />
<meta itemprop="image" content="{{ $coverImage }}" />

<!-- Schema.org -->
<meta itemprop="name" content="{{ $title }}" />
<meta itemprop="description" content="{{ $description }}" />
<meta itemprop="image" content="{{ $coverImage }}" />
