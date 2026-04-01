<?php

namespace BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\RelationManagers;

use BlackpigCreatif\Magistere\Enums\ExtraPer;
use BlackpigCreatif\Magistere\Filament\Concerns\HasTranslatableFields;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExtrasRelationManager extends RelationManager
{
    use HasTranslatableFields;

    protected static string $relationship = 'extras';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            static::translatableInput('title')
                ->label('Title')
                ->required()
                ->maxLength(255),
            static::translatableTextarea('description')
                ->label('Description')
                ->rows(3)
                ->nullable(),
            Grid::make(3)
                ->schema([
                    TextInput::make('price')
                        ->numeric()
                        ->prefix('€')
                        ->required()
                        ->default(0),
                    TextInput::make('currency')
                        ->maxLength(3)
                        ->default('EUR'),
                    TextInput::make('capacity')
                        ->numeric()
                        ->nullable()
                        ->helperText('Leave blank for unlimited'),
                ]),
            Grid::make(3)
                ->schema([
                    Select::make('per')
                        ->options(ExtraPer::class)
                        ->required()
                        ->default(ExtraPer::Booking),
                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0),
                    Toggle::make('is_required')
                        ->label('Required')
                        ->default(false),
                ]),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->formatStateUsing(fn (mixed $state): string => is_array($state)
                        ? ($state[app()->getLocale()] ?? $state['en'] ?? array_values($state)[0] ?? '')
                        : (string) $state)
                    ->searchable(),
                TextColumn::make('price')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('per')
                    ->badge(),
                TextColumn::make('capacity')
                    ->placeholder('Unlimited'),
                IconColumn::make('is_required')
                    ->label('Required')
                    ->boolean(),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('per')
                    ->options(ExtraPer::class),
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
