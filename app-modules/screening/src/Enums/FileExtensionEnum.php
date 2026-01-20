<?php

declare(strict_types=1);

namespace He4rt\Screening\Enums;

use Filament\Support\Contracts\HasLabel;

/**
 * Allowed file extensions for document uploads in screening questions.
 */
enum FileExtensionEnum: string implements HasLabel
{
    case Pdf = 'pdf';
    case Doc = 'doc';
    case Docx = 'docx';
    case Txt = 'txt';
    case Rtf = 'rtf';

    /**
     * Get all MIME types for an array of extensions.
     *
     * @param  array<int, self>  $extensions
     * @return array<int, string>
     */
    public static function getMimeTypes(array $extensions): array
    {
        return array_map(fn (self $ext) => $ext->getMimeType(), $extensions);
    }

    public function getLabel(): string
    {
        return match ($this) {
            self::Pdf => 'PDF (.pdf)',
            self::Doc => 'Word 97-2003 (.doc)',
            self::Docx => 'Word (.docx)',
            self::Txt => 'Text (.txt)',
            self::Rtf => 'Rich Text (.rtf)',
        };
    }

    public function getMimeType(): string
    {
        return match ($this) {
            self::Pdf => 'application/pdf',
            self::Doc => 'application/msword',
            self::Docx => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            self::Txt => 'text/plain',
            self::Rtf => 'application/rtf',
        };
    }
}
