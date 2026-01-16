<?php

declare(strict_types=1);

namespace He4rt\Admin\Filament\Resources\Recruitment\Candidates;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Admin\Filament\Resources\Recruitment\Candidates\Pages\CreateCandidate;
use He4rt\Admin\Filament\Resources\Recruitment\Candidates\Pages\EditCandidate;
use He4rt\Admin\Filament\Resources\Recruitment\Candidates\Pages\ListCandidates;
use He4rt\Admin\Filament\Resources\Recruitment\Candidates\Schemas\CandidateForm;
use He4rt\Admin\Filament\Resources\Recruitment\Candidates\Tables\CandidatesTable;
use He4rt\Candidates\Models\Candidate;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Override;
use UnitEnum;

class CandidateResource extends Resource
{
    protected static ?string $model = Candidate::class;

    protected static ?string $slug = 'candidates';

    protected static string|null|UnitEnum $navigationGroup = 'Recruitment';

    protected static ?string $recordTitleAttribute = 'headline';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserGroup;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return CandidateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CandidatesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCandidates::route('/'),
            'create' => CreateCandidate::route('/create'),
            'edit' => EditCandidate::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'user.email', 'phone', 'headline'];
    }

    public static function getModelLabel(): string
    {
        return __('candidates::filament.resource.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('candidates::filament.resource.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('candidates::filament.resource.navigation_label');
    }

    /**
     * @return Builder<Candidate>
     */
    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['user'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchResultTitle(Model $record): Htmlable|string
    {
        return $record->user->name ?? $record->headline ?? (string) $record->getKey();
    }
}
