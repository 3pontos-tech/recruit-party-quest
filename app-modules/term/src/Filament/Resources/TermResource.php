<?php

declare(strict_types=1);

namespace He4rt\Term\Filament\Resources;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use He4rt\Term\Filament\Resources\Pages\CreateTerm;
use He4rt\Term\Filament\Resources\Pages\EditTerm;
use He4rt\Term\Filament\Resources\Pages\ListTerms;
use He4rt\Term\Filament\Resources\Schemas\TermForm;
use He4rt\Term\Filament\Resources\Tables\TermsTable;
use He4rt\Term\Models\Term;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Override;
use UnitEnum;

class TermResource extends Resource
{
    protected static ?string $model = Term::class;

    protected static ?string $slug = 'terms';

    protected static string|null|UnitEnum $navigationGroup = 'Content';

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    public static function form(Schema $schema): Schema
    {
        return TermForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TermsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTerms::route('/'),
            'create' => CreateTerm::route('/create'),
            'edit' => EditTerm::route('/{record}/edit'),
        ];
    }

    public static function getModelLabel(): string
    {
        return __('term::filament.resource.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('term::filament.resource.plural_label');
    }

    public static function getNavigationLabel(): string
    {
        return __('term::filament.resource.navigation_label');
    }

    /**
     * @return Builder<Term>
     */
    #[Override]
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
