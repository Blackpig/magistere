<?php

namespace BlackpigCreatif\Magistere\Filament\Resources;

use BlackpigCreatif\Magistere\Enums\BookingStatus;
use BlackpigCreatif\Magistere\Enums\PaymentStatus;
use BlackpigCreatif\Magistere\Filament\Concerns\BelongsToMagistereNavigationGroup;
use BlackpigCreatif\Magistere\Filament\Resources\BookingResource\Pages;
use BlackpigCreatif\Magistere\Filament\Resources\BookingResource\RelationManagers\AttendeesRelationManager;
use BlackpigCreatif\Magistere\Filament\Resources\BookingResource\RelationManagers\PaymentsRelationManager;
use BlackpigCreatif\Magistere\Models\Booking;
use BlackpigCreatif\Magistere\Models\Workshop;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BookingResource extends Resource
{
    use BelongsToMagistereNavigationGroup;

    protected static ?string $model = Booking::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Workshop')
                ->schema([
                    Select::make('workshop_id')
                        ->label('Workshop')
                        ->relationship('workshop', 'slug')
                        ->getOptionLabelFromRecordUsing(fn (Workshop $record): string => sprintf(
                            '%s — %s',
                            is_array($record->display_title)
                                ? ($record->display_title[app()->getLocale()] ?? $record->display_title['en'] ?? array_values($record->display_title)[0] ?? '')
                                : (string) ($record->display_title ?? $record->slug),
                            $record->starts_at?->format('d M Y') ?? '—',
                        ))
                        ->searchable()
                        ->preload()
                        ->required(),
                ]),

            Section::make('Primary Contact')
                ->columns(2)
                ->schema([
                    TextInput::make('contact_first_name')
                        ->label('First Name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('contact_last_name')
                        ->label('Last Name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('contact_email')
                        ->label('Email')
                        ->email()
                        ->required()
                        ->maxLength(255),
                    TextInput::make('contact_phone')
                        ->label('Phone')
                        ->tel()
                        ->nullable()
                        ->maxLength(50),
                    TextInput::make('contact_organisation')
                        ->label('Organisation')
                        ->nullable()
                        ->maxLength(255)
                        ->columnSpanFull(),
                ]),

            Section::make('Status')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->options(BookingStatus::class)
                        ->required()
                        ->default(BookingStatus::Pending),
                    Select::make('payment_status')
                        ->options(PaymentStatus::class)
                        ->disabled()
                        ->dehydrated(false)
                        ->helperText('Recalculated automatically from the payment ledger'),
                ]),

            Section::make('Pricing')
                ->columns(3)
                ->schema([
                    TextInput::make('attendee_count')
                        ->label('Attendees')
                        ->numeric()
                        ->required()
                        ->default(1)
                        ->minValue(1),
                    TextInput::make('subtotal')
                        ->label('Subtotal')
                        ->numeric()
                        ->prefix('€')
                        ->required()
                        ->default(0),
                    TextInput::make('currency')
                        ->maxLength(3)
                        ->default('EUR'),
                ]),

            Section::make('Notes')
                ->schema([
                    Textarea::make('notes')
                        ->label('Customer Notes')
                        ->rows(3)
                        ->nullable(),
                    Textarea::make('internal_notes')
                        ->label('Internal Notes')
                        ->rows(3)
                        ->nullable(),
                ]),

            Section::make('Consent')
                ->columns(2)
                ->schema([
                    Toggle::make('gdpr_consent')
                        ->label('GDPR Consent')
                        ->required(),
                    Toggle::make('marketing_consent')
                        ->label('Marketing Consent'),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono'),
                TextColumn::make('contact_full_name')
                    ->label('Contact')
                    ->searchable(['contact_first_name', 'contact_last_name'])
                    ->sortable('contact_last_name'),
                TextColumn::make('contact_email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('workshop.starts_at')
                    ->label('Workshop Date')
                    ->dateTime('d M Y')
                    ->sortable(),
                TextColumn::make('attendee_count')
                    ->label('Attendees')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('subtotal')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('payment_status')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(BookingStatus::class),
                SelectFilter::make('payment_status')
                    ->options(PaymentStatus::class),
                SelectFilter::make('workshop')
                    ->relationship('workshop', 'slug')
                    ->getOptionLabelFromRecordUsing(fn (Workshop $record): string => sprintf(
                        '%s — %s',
                        is_array($record->display_title)
                            ? ($record->display_title[app()->getLocale()] ?? $record->display_title['en'] ?? array_values($record->display_title)[0] ?? '')
                            : (string) ($record->display_title ?? $record->slug),
                        $record->starts_at?->format('d M Y') ?? '—',
                    ))
                    ->searchable()
                    ->preload(),
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
            ->with(['workshop'])
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getRelations(): array
    {
        return [
            AttendeesRelationManager::class,
            PaymentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
        ];
    }
}
