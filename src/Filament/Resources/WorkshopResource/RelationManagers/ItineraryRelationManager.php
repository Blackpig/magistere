<?php

namespace BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\RelationManagers;

use BlackpigCreatif\Magistere\Filament\Concerns\HasTranslatableFields;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItineraryRelationManager extends RelationManager
{
    use HasTranslatableFields;

    protected static string $relationship = 'itineraryItems';

    protected static ?string $title = 'Itinerary';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(4)
                ->schema([
                    TextInput::make('day')
                        ->label('Day')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->default(1),
                    TimePicker::make('start_time')
                        ->label('Start')
                        ->seconds(false)
                        ->nullable(),
                    TimePicker::make('end_time')
                        ->label('End')
                        ->seconds(false)
                        ->nullable(),
                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),
                ]),
            static::translatableInput('title')
                ->label('Title')
                ->required()
                ->maxLength(255),
            static::translatableTextarea('description')
                ->label('Description')
                ->rows(3)
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('day')
            ->columns([
                TextColumn::make('day')
                    ->label('Day')
                    ->sortable(),
                TextColumn::make('start_time')
                    ->label('Start')
                    ->time('H:i'),
                TextColumn::make('end_time')
                    ->label('End')
                    ->time('H:i'),
                TextColumn::make('title')
                    ->formatStateUsing(fn (mixed $state): string => is_array($state)
                        ? ($state[app()->getLocale()] ?? $state['en'] ?? array_values($state)[0] ?? '')
                        : (string) $state)
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
