<?php

declare(strict_types=1);

namespace He4rt\Screening\QuestionTypes\Settings;

/**
 * Settings for File Upload question type.
 */
readonly class FileUploadSettings
{
    /**
     * @param  array<int, string>  $allowedExtensions
     */
    public function __construct(
        public ?int $maxSizeKb = null,
        public int $maxFiles = 1,
        public array $allowedExtensions = [],
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            maxSizeKb: isset($data['max_size_kb']) ? (int) $data['max_size_kb'] : null,
            maxFiles: (int) ($data['max_files'] ?? 1),
            allowedExtensions: $data['allowed_extensions'] ?? [],
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'max_size_kb' => $this->maxSizeKb,
            'max_files' => $this->maxFiles,
            'allowed_extensions' => $this->allowedExtensions,
        ];
    }
}
