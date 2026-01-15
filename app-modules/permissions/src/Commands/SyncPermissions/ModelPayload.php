<?php

declare(strict_types=1);

namespace He4rt\Permissions\Commands\SyncPermissions;

readonly class ModelPayload
{
    public function __construct(
        public string $name,
        public string $resource,
        public string $resourceGroup,
    ) {}

    /**
     * @return array{name: string, resource: string, resource_group: string}
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'resource' => $this->resource,
            'resource_group' => $this->resourceGroup,
        ];
    }
}
