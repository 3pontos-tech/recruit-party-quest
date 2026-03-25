<?php

declare(strict_types=1);

namespace He4rt\PluginSeo\Contracts;

use He4rt\PluginSeo\Metadata;

interface HasMetadata
{
    public function getMetadata(): Metadata;
}
