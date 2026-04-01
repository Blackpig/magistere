<?php

namespace BlackpigCreatif\Magistere\Filament\Resources\BookingResource\RelationManagers;

use BlackpigCreatif\Magistere\Filament\Actions\CheckInAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AttendeesRelationManager extends RelationManager
{
    protected static string $relationship = 'attendees';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)
                ->schema([
                    TextInput::make('first_name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('last_name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->nullable()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->tel()
                        ->nullable()
                        ->maxLength(50),
                ]),
            Toggle::make('is_primary_contact')
                ->label('Primary Contact'),
            Textarea::make('dietary_requirements')
                ->rows(2)
                ->nullable(),
            Textarea::make('accessibility_requirements')
                ->rows(2)
                ->nullable(),
            Textarea::make('notes')
                ->rows(2)
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name']),
                TextColumn::make('email')
                    ->searchable()
                    ->placeholder('—'),
                IconColumn::make('is_primary_contact')
                    ->label('Primary')
                    ->boolean(),
                TextColumn::make('dietary_requirements')
                    ->placeholder('None')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('checked_in_at')
                    ->label('Checked In')
                    ->dateTime('d M Y H:i')
                    ->placeholder('Not checked in'),
                CheckInAction::make(),
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
