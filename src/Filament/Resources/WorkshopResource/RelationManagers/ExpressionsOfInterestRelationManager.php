<?php

namespace BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\RelationManagers;

use BlackpigCreatif\Magistere\Enums\EoiSource;
use BlackpigCreatif\Magistere\Enums\EoiStatus;
use BlackpigCreatif\Magistere\Filament\Actions\ConvertToBookingAction;
use BlackpigCreatif\Magistere\Filament\Actions\NotifyInterestListBulkAction;
use BlackpigCreatif\Magistere\Filament\Actions\OfferPlaceAction;
use BlackpigCreatif\Magistere\Filament\Actions\ResendNotificationAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExpressionsOfInterestRelationManager extends RelationManager
{
    protected static string $relationship = 'expressionsOfInterest';

    protected static ?string $title = 'Expressions of Interest';

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
                        ->required()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->tel()
                        ->nullable()
                        ->maxLength(50),
                ]),
            Grid::make(2)
                ->schema([
                    TextInput::make('attendee_count')
                        ->label('Attendees')
                        ->numeric()
                        ->required()
                        ->default(1)
                        ->minValue(1),
                    Select::make('source')
                        ->options(EoiSource::class)
                        ->required()
                        ->default(EoiSource::Interest),
                    Select::make('status')
                        ->options(EoiStatus::class)
                        ->required()
                        ->default(EoiStatus::New),
                ]),
            Textarea::make('message')
                ->rows(3)
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('attendee_count')
                    ->label('Attendees')
                    ->numeric(),
                TextColumn::make('source')
                    ->badge(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('notified_at')
                    ->label('Notified')
                    ->dateTime('d M Y')
                    ->placeholder('Not yet'),
                TextColumn::make('token_expires_at')
                    ->label('Link Expires')
                    ->dateTime('d M Y H:i')
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('source')
                    ->options(EoiSource::class),
                SelectFilter::make('status')
                    ->options(EoiStatus::class),
            ])
            ->recordActions([
                EditAction::make(),
                OfferPlaceAction::make(),
                ResendNotificationAction::make(),
                ConvertToBookingAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                CreateAction::make(),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    NotifyInterestListBulkAction::make(),
                ]),
            ]);
    }
}
