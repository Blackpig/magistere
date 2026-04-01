<?php

namespace BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\RelationManagers;

use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TrainersRelationManager extends RelationManager
{
    protected static string $relationship = 'trainers';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->required()
                ->maxLength(255),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('pivot.role')
                    ->label('Role')
                    ->placeholder('—'),
                TextColumn::make('pivot.sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('role')
                            ->nullable()
                            ->maxLength(100)
                            ->placeholder('e.g. Lead, Assistant'),
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0),
                    ]),
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
