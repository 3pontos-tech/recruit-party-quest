<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Recruitment\Applications\Pages;

use Filament\Resources\Pages\CreateRecord;
use He4rt\Admin\Filament\Resources\Recruitment\Applications\ApplicationResource;
use Illuminate\Support\Str;

class CreateApplication extends CreateRecord
{
    protected static string $resource = ApplicationResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (blank($data['tracking_code'] ?? null)) {
            $data['tracking_code'] = 'APP-'.mb_strtoupper(Str::random(8));
        }

        return $data;
    }
}
