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

    /**
     * @return array<string, string>
     */
    public static function allIcons(): array
    {
        $icons = [];

        foreach (self::cases() as $case) {
            $icons = array_merge($icons, $case->icons());
        }

        return $icons;
    }

    public function getLabel(): string
    {
        return __('links::types.'.$this->value.'.label');
    }

    /**
     * @return array<string, string>
     */
    public function icons(): array
    {
        return match ($this) {
            self::Website => [
                'heroicon-o-globe-alt' => 'Globe',
            ],
            self::Social => [
                'heroicon-o-user-group' => 'User group',
                'heroicon-o-chat-bubble-left-right' => 'Chat',
                'heroicon-o-camera' => 'Camera',
                'heroicon-o-video-camera' => 'Video',
            ],
            self::Email => [
                'heroicon-o-envelope' => 'Envelope',
            ],
            self::Phone => [
                'heroicon-o-phone' => 'Phone',
                'heroicon-o-device-phone-mobile' => 'Mobile',
                'heroicon-o-chat-bubble-left-right' => 'Chat',
            ],
            self::Document => [
                'heroicon-o-document' => 'Document',
                'heroicon-o-clipboard-document' => 'Clipboard',
            ],
            self::External => [
                'heroicon-o-globe-alt' => 'Globe',
                'heroicon-o-link' => 'Link',
            ],
            self::Internal => [
                'heroicon-o-link' => 'Link',
                'heroicon-o-home' => 'Home',
            ],
            self::CTA => [
                'heroicon-o-cursor-arrow-rays' => 'Click',
                'heroicon-o-arrow-right' => 'Arrow right',
            ],
        };
    }
}
