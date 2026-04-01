<?php

namespace BlackpigCreatif\Magistere\Filament\Resources;

use BlackpigCreatif\Magistere\Filament\Concerns\BelongsToMagistereNavigationGroup;
use BlackpigCreatif\Magistere\Filament\Concerns\HasTranslatableFields;
use BlackpigCreatif\Magistere\Filament\Resources\LocationResource\Pages;
use BlackpigCreatif\Magistere\Models\Location;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LocationResource extends Resource
{
    use BelongsToMagistereNavigationGroup;
    use HasTranslatableFields;

    protected static ?string $model = Location::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?int $navigationSort = 50;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Address')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),
                    TextInput::make('address_line_1')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('address_line_2')
                        ->maxLength(255),
                    TextInput::make('city')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('region')
                        ->maxLength(255),
                    TextInput::make('postcode')
                        ->maxLength(20),
                    Select::make('country')
                        ->required()
                        ->searchable()
                        ->options(static::countryOptions())
                        ->default('FR'),
                ]),

            Section::make('Coordinates & Capacity')
                ->columns(3)
                ->schema([
                    TextInput::make('lat')
                        ->label('Latitude')
                        ->numeric()
                        ->nullable(),
                    TextInput::make('lng')
                        ->label('Longitude')
                        ->numeric()
                        ->nullable(),
                    TextInput::make('max_capacity')
                        ->label('Max Capacity')
                        ->numeric()
                        ->nullable()
                        ->helperText('Leave blank for no venue limit'),
                ]),

            Section::make('Details')
                ->schema([
                    static::translatableTextarea('description')
                        ->rows(4)
                        ->nullable(),
                    TextInput::make('website')
                        ->url()
                        ->maxLength(255)
                        ->nullable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('city')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('country')
                    ->sortable(),
                TextColumn::make('max_capacity')
                    ->label('Capacity')
                    ->numeric()
                    ->placeholder('Unlimited')
                    ->sortable(),
                TextColumn::make('workshops_count')
                    ->label('Workshops')
                    ->counts('workshops')
                    ->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLocations::route('/'),
            'create' => Pages\CreateLocation::route('/create'),
            'edit' => Pages\EditLocation::route('/{record}/edit'),
        ];
    }

    /** @return array<string, string> */
    protected static function countryOptions(): array
    {
        return [
            'AT' => 'Austria', 'BE' => 'Belgium', 'HR' => 'Croatia',
            'CY' => 'Cyprus', 'CZ' => 'Czechia', 'DK' => 'Denmark',
            'EE' => 'Estonia', 'FI' => 'Finland', 'FR' => 'France',
            'DE' => 'Germany', 'GR' => 'Greece', 'HU' => 'Hungary',
            'IE' => 'Ireland', 'IT' => 'Italy', 'LV' => 'Latvia',
            'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MT' => 'Malta',
            'NL' => 'Netherlands', 'PL' => 'Poland', 'PT' => 'Portugal',
            'RO' => 'Romania', 'SK' => 'Slovakia', 'SI' => 'Slovenia',
            'ES' => 'Spain', 'SE' => 'Sweden', 'GB' => 'United Kingdom',
            'CH' => 'Switzerland', 'NO' => 'Norway',
        ];
    }
}
