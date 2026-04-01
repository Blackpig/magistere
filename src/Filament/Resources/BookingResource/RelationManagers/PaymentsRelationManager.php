<?php

namespace BlackpigCreatif\Magistere\Filament\Resources\BookingResource\RelationManagers;

use BlackpigCreatif\Magistere\Enums\PaymentMethod;
use BlackpigCreatif\Magistere\Enums\PaymentType;
use BlackpigCreatif\Magistere\Models\Booking;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(2)
                ->schema([
                    TextInput::make('amount')
                        ->numeric()
                        ->prefix('€')
                        ->required(),
                    TextInput::make('currency')
                        ->maxLength(3)
                        ->default('EUR'),
                    Select::make('method')
                        ->options(PaymentMethod::class)
                        ->required(),
                    Select::make('type')
                        ->options(PaymentType::class)
                        ->required()
                        ->default(PaymentType::Payment),
                    DatePicker::make('paid_at')
                        ->required()
                        ->default(now()),
                    TextInput::make('reference')
                        ->nullable()
                        ->maxLength(255)
                        ->helperText('Bank transfer ref, cheque number, etc.'),
                ]),
            Textarea::make('notes')
                ->rows(3)
                ->nullable(),
        ]);
    }

    public function table(Table $table): Table
    {
        /** @var Booking $booking */
        $booking = $this->getOwnerRecord();

        $balance = (float) $booking->subtotal - (float) $booking->amount_paid;
        $balanceFormatted = number_format(abs($balance), 2);
        $balanceLabel = $balance > 0
            ? "Outstanding: €{$balanceFormatted}"
            : ($balance < 0 ? "Overpaid: €{$balanceFormatted}" : 'Fully paid');

        return $table
            ->heading('Payments — ' . $balanceLabel)
            ->defaultSort('paid_at', 'desc')
            ->columns([
                TextColumn::make('paid_at')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('type')
                    ->badge(),
                TextColumn::make('method')
                    ->badge(),
                TextColumn::make('amount')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('reference')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('recordedBy.name')
                    ->label('Recorded By')
                    ->placeholder('—'),
                TextColumn::make('notes')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options(PaymentType::class),
                SelectFilter::make('method')
                    ->options(PaymentMethod::class),
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
