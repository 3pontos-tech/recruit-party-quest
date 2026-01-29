<?php

declare(strict_types=1);

namespace He4rt\Links;

enum LinkTypeEnum: string
{
    case Website = 'website';
    case Social = 'social';
    case Email = 'email';
    case Phone = 'phone';
    case Document = 'document';
    case External = 'external';
    case Internal = 'internal';
    case CTA = 'cta';

    public function getLabel(): string
    {
        return __('links::types.'.$this->value.'.label');
    }
}
