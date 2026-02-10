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
                'heroicon-o-home' => 'Home',
                'heroicon-o-link' => 'Link',
                'heroicon-o-window' => 'Window',
                'heroicon-o-computer-desktop' => 'Desktop',
            ],
            self::Social => [
                'heroicon-o-user-group' => 'User group',
                'heroicon-o-chat-bubble-left-right' => 'Chat',
                'heroicon-o-share' => 'Share',
                'heroicon-o-heart' => 'Heart',
                'heroicon-o-hand-thumb-up' => 'Thumb up',
                'heroicon-o-megaphone' => 'Megaphone',
                'heroicon-o-hashtag' => 'Hashtag',
                'heroicon-o-at-symbol' => 'At symbol',
                'heroicon-o-camera' => 'Camera',
                'heroicon-o-video-camera' => 'Video',
            ],
            self::Email => [
                'heroicon-o-envelope' => 'Envelope',
                'heroicon-o-envelope-open' => 'Envelope open',
                'heroicon-o-inbox' => 'Inbox',
                'heroicon-o-inbox-arrow-down' => 'Inbox arrow down',
                'heroicon-o-paper-airplane' => 'Paper airplane',
                'heroicon-o-at-symbol' => 'At symbol',
            ],
            self::Phone => [
                'heroicon-o-phone' => 'Phone',
                'heroicon-o-phone-arrow-down-left' => 'Phone incoming',
                'heroicon-o-phone-arrow-up-right' => 'Phone outgoing',
                'heroicon-o-device-phone-mobile' => 'Mobile',
                'heroicon-o-chat-bubble-left-right' => 'Chat',
            ],
            self::Document => [
                'heroicon-o-document' => 'Document',
                'heroicon-o-document-text' => 'Document text',
                'heroicon-o-document-check' => 'Document check',
                'heroicon-o-document-arrow-down' => 'Document download',
                'heroicon-o-document-duplicate' => 'Document duplicate',
                'heroicon-o-paper-clip' => 'Paper clip',
                'heroicon-o-folder' => 'Folder',
                'heroicon-o-clipboard-document' => 'Clipboard',
            ],
            self::External => [
                'heroicon-o-arrow-top-right-on-square' => 'External link',
                'heroicon-o-globe-alt' => 'Globe',
                'heroicon-o-link' => 'Link',
                'heroicon-o-arrow-right' => 'Arrow right',
                'heroicon-o-rocket-launch' => 'Rocket',
            ],
            self::Internal => [
                'heroicon-o-link' => 'Link',
                'heroicon-o-home' => 'Home',
                'heroicon-o-building-office' => 'Building',
                'heroicon-o-cog-6-tooth' => 'Cog',
                'heroicon-o-squares-2x2' => 'Grid',
                'heroicon-o-rectangle-group' => 'Layout',
            ],
            self::CTA => [
                'heroicon-o-cursor-arrow-rays' => 'Click',
                'heroicon-o-rocket-launch' => 'Rocket',
                'heroicon-o-bolt' => 'Bolt',
                'heroicon-o-fire' => 'Fire',
                'heroicon-o-star' => 'Star',
                'heroicon-o-sparkles' => 'Sparkles',
                'heroicon-o-arrow-right' => 'Arrow right',
                'heroicon-o-play' => 'Play',
            ],
        };
    }
}
