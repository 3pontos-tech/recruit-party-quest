<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Ai\AiAssistants\Pages;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use He4rt\Admin\Filament\Resources\Ai\AiAssistants\AiAssistantResource;
use He4rt\Admin\Filament\Resources\Ai\AiAssistants\Forms\AiAssistantForm;
use He4rt\Ai\Models\AiAssistant;

/**
 * @method AiAssistant getRecord()
 */
final class EditAiAssistant extends EditRecord
{
    protected static string $resource = AiAssistantResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return true;
    }

    public function form(Schema $schema): Schema
    {
        return resolve(AiAssistantForm::class)->form($schema);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('archive')
                ->color('danger')
                ->action(function (): void {
                    $assistant = $this->getRecord();
                    $assistant->archived_at = now();
                    $assistant->save();

                    Notification::make()
                        ->title('Assistant archived')
                        ->success()
                        ->send();
                })
                ->hidden(fn (): bool => (bool) $this->getRecord()->archived_at),
            Action::make('restore')
                ->action(function (): void {
                    $assistant = $this->getRecord();
                    $assistant->archived_at = null;
                    $assistant->save();

                    Notification::make()
                        ->title('Assistant restored')
                        ->success()
                        ->send();
                }),
        ];
    }
}
