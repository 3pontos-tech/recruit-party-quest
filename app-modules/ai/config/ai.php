<?php

declare(strict_types=1);

use Prism\Prism\Enums\Provider;

return [
    /**
     * @see https://docs.cloud.llamaindex.ai/llamaparse/features/supported_document_types
     */
    'supported_file_types' => [
        // Base types
        'application/pdf',

        // Documents and presentations
        'application/x-t602',
        'application/x-abiword',
        'image/cgm',
        'application/x-cwk',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-word.document.macroEnabled.12',
        'application/vnd.ms-word.template.macroEnabled.12',
        'application/x-hwp',
        'application/vnd.apple.keynote',
        'application/vnd.lotus-wordpro',
        'application/x-mwrite',
        'application/vnd.apple.pages',
        'application/vnd.powerbuilder6',
        'application/vnd.ms-powerpoint',
        'application/vnd.ms-powerpoint.presentation.macroEnabled.12',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/vnd.ms-powerpoint.template.macroEnabled.12',
        'application/vnd.openxmlformats-officedocument.presentationml.template',
        'application/rtf',
        'application/vnd.stardivision.draw',
        'application/vnd.stardivision.impress',
        'application/vnd.stardivision.writer',
        'application/vnd.stardivision.writer-global',
        'application/vnd.sun.xml.impress.template',
        'application/vnd.sun.xml.impress',
        'application/vnd.sun.xml.writer',
        'application/vnd.sun.xml.writer.template',
        'application/vnd.sun.xml.writer.global',
        'text/plain',
        'application/x-uof',
        'application/x-uop',
        'application/x-uo',
        'application/vnd.wordperfect',
        'application/vnd.ms-works',
        'application/xml',
        'text/xml',
        'application/epub+zip',

        // Images
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/bmp',
        'image/svg+xml',
        'image/tiff',
        'image/webp',
        'text/html',

        // Spreadsheets
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'application/vnd.ms-excel.sheet.macroEnabled.12',
        'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
        'text/csv',
        'application/x-dif',
        'text/spreadsheet',
        'text/plain',
        'application/vnd.apple.numbers',
        'application/x-et',
        'application/vnd.oasis.opendocument.spreadsheet',
        'application/vnd.uos.spreadsheet',
        'application/dbf',
        'application/x-dbf',
        'application/vnd.lotus-1-2-3',
        'application/x-quattro-pro',
        'application/x-ezchart',
        'text/tab-separated-values',

        // Audio
        'audio/mpeg',
        'audio/mp4',
        'video/mp4',
        'video/mpeg',
        'audio/wav',
        'audio/x-wav',
        'audio/webm',
        'video/webm',
    ],
    'provider' => [
        'gemini' => [
            'enum' => env('AI_PROVIDER', Provider::OpenAI->name),
            'model' => env('AI_MODEL', 'gpt-5-nano'),
        ],
    ],
];
