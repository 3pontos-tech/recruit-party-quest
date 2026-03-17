<?php

declare(strict_types=1);

namespace App\Support\UrlGenerator;

use Spatie\MediaLibrary\Support\UrlGenerator\DefaultUrlGenerator;

class PrivateR2UrlGenerator extends DefaultUrlGenerator
{
    public function getUrl(): string
    {
        if ($this->media->disk === 'r2') {
            return $this->getTemporaryUrl(now()->addMinutes(config('media-library.private_url_expiry_minutes', 30)));
        }

        return parent::getUrl();
    }
}
