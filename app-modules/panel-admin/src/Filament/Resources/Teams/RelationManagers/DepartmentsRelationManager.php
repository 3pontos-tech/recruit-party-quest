<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Teams\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class DepartmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'departments';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('teams::filament.relation_managers.departments.title');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('teams::filament.department.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('description')
                    ->label(__('teams::filament.department.fields.description'))
                    ->limit(50),
                TextColumn::make('headUser.name')
                    ->label(__('teams::filament.department.fields.head_user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('requisitions_count')
                    ->counts('requisitions')
                    ->label(__('teams::filament.department.fields.requisitions_count'))
                    ->badge(),
                TextColumn::make('created_at')
                    ->label(__('teams::filament.department.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                ViewAction::make()
                    ->schema($this->getDepartmentViewSchema()),
                EditAction::make()
                    ->schema($this->getDepartmentFormSchema()),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->schema($this->getDepartmentFormSchema()),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function getModelLabel(): ?string
    {
        return __('teams::filament.department.label');
    }

    protected static function getPluralModelLabel(): ?string
    {
        return __('teams::filament.department.plural_label');
    }

    /**
     * Shared schema used by Create/Edit modals.
     *
     * @return array<int, mixed>
     */
    protected function getDepartmentFormSchema(): array
    {
        return [
            TextInput::make('name')
                ->label(__('teams::filament.department.fields.name'))
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->label(__('teams::filament.department.fields.description'))
                ->required()
                ->maxLength(255)
                ->rows(4),
            Select::make('head_user_id')
                ->label(__('teams::filament.department.fields.head_user'))
                ->required()
                ->searchable()
                ->preload()
                ->options(fn (): array => $this->getOwnerRecord()->members()
                    ->orderBy('name')
                    ->pluck('name', 'id')
                    ->all()),
        ];
    }

    /**
     * Schema used by the View modal.
     *
     * @return array<int, mixed>
     */
    protected function getDepartmentViewSchema(): array
    {
        return [
            Section::make(__('teams::filament.department.sections.identity'))
                ->icon(Heroicon::BuildingOffice)
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('name')
                                ->label(__('teams::filament.department.fields.name'))
                                ->weight('bold')
                                ->size(TextSize::Large),
                            TextEntry::make('description')
                                ->label(__('teams::filament.department.fields.description'))
                                ->columnSpanFull()
                                ->placeholder('-'),
                        ]),
                ]),
            Section::make(__('teams::filament.department.sections.management'))
                ->icon(Heroicon::UserCircle)
                ->schema([
                    TextEntry::make('headUser.name')
                        ->label(__('teams::filament.department.fields.head_user')),
                ]),
            Section::make(__('teams::filament.department.sections.metrics'))
                ->icon(Heroicon::ChartBar)
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextEntry::make('requisitions_count')
                                ->label(__('teams::filament.department.fields.requisitions_count'))
                                ->counts('requisitions')
                                ->badge(),
                        ]),
                ]),
            Section::make(__('teams::filament.department.sections.metadata'))
                ->collapsed()
                ->icon(Heroicon::Clock)
                ->schema([
                    TextEntry::make('created_at')
                        ->label(__('teams::filament.department.fields.created_at'))
                        ->dateTime(),
                ]),
        ];
    }
}
