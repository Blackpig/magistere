<?php

namespace BlackpigCreatif\Magistere\Filament\Resources;

use BlackpigCreatif\Magistere\Enums\WorkshopStatus;
use BlackpigCreatif\Magistere\Filament\Concerns\BelongsToMagistereNavigationGroup;
use BlackpigCreatif\Magistere\Filament\Concerns\HasTranslatableFields;
use BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\Pages;
use BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\RelationManagers\BookingsRelationManager;
use BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\RelationManagers\ExpressionsOfInterestRelationManager;
use BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\RelationManagers\ExtrasRelationManager;
use BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\RelationManagers\ItineraryRelationManager;
use BlackpigCreatif\Magistere\Filament\Resources\WorkshopResource\RelationManagers\TrainersRelationManager;
use BlackpigCreatif\Magistere\Models\Course;
use BlackpigCreatif\Magistere\Models\Location;
use BlackpigCreatif\Magistere\Models\Workshop;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class WorkshopResource extends Resource
{
    use BelongsToMagistereNavigationGroup;
    use HasTranslatableFields;

    protected static ?string $model = Workshop::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Course')
                ->columns(2)
                ->schema([
                    Select::make('course_id')
                        ->label('Course')
                        ->relationship('course', 'title')
                        ->getOptionLabelFromRecordUsing(fn (Course $record): string => is_array($record->title)
                            ? ($record->title[app()->getLocale()] ?? $record->title['en'] ?? array_values($record->title)[0] ?? '')
                            : (string) $record->title)
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->columnSpanFull(),

                    static::translatableInput('title')
                        ->label('Title Override')
                        ->maxLength(255)
                        ->nullable()
                        ->helperText('Leave blank to inherit the course title'),

                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(Workshop::class, 'slug', ignoreRecord: true),
                ]),

            Section::make('Schedule')
                ->columns(2)
                ->schema([
                    DateTimePicker::make('starts_at')
                        ->required()
                        ->live(onBlur: true),
                    DateTimePicker::make('ends_at')
                        ->required()
                        ->after('starts_at'),
                    DateTimePicker::make('registration_opens_at')
                        ->nullable(),
                    DateTimePicker::make('registration_closes_at')
                        ->nullable()
                        ->after('registration_opens_at'),
                ]),

            Section::make('Location & Capacity')
                ->columns(2)
                ->schema([
                    Select::make('location_id')
                        ->label('Location')
                        ->relationship('location', 'name')
                        ->searchable()
                        ->preload()
                        ->nullable()
                        ->live()
                        ->columnSpanFull(),

                    TextInput::make('min_capacity')
                        ->label('Min Capacity Override')
                        ->numeric()
                        ->nullable()
                        ->minValue(1),

                    TextInput::make('max_capacity')
                        ->label('Max Capacity Override')
                        ->numeric()
                        ->nullable()
                        ->minValue(1)
                        ->live(onBlur: true),

                    Placeholder::make('capacity_hint')
                        ->label('Effective Capacity')
                        ->content(function (Get $get): string {
                            $courseId = $get('course_id');
                            $locationId = $get('location_id');
                            $workshopMax = $get('max_capacity');

                            $course = $courseId ? Course::find($courseId) : null;

                            if (! $course) {
                                return 'Select a course to see capacity constraints.';
                            }

                            $location = $locationId ? Location::find($locationId) : null;

                            $parts = ['Course max: ' . $course->max_capacity];

                            if ($location?->max_capacity !== null) {
                                $parts[] = 'Location max: ' . $location->max_capacity;
                            }

                            if ($workshopMax) {
                                $parts[] = 'Workshop max: ' . $workshopMax;
                            }

                            $overrides = array_filter(
                                [(int) $workshopMax ?: null, $location?->max_capacity],
                                fn ($v) => $v !== null,
                            );

                            $effective = empty($overrides)
                                ? $course->max_capacity
                                : min($course->max_capacity, ...$overrides);

                            $parts[] = '**Effective: ' . $effective . '**';

                            return implode(' · ', $parts);
                        })
                        ->columnSpanFull(),
                ]),

            Section::make('Pricing')
                ->columns(3)
                ->schema([
                    TextInput::make('price')
                        ->label('Price Override')
                        ->numeric()
                        ->prefix('€')
                        ->nullable()
                        ->helperText('Leave blank to inherit course base price'),
                    TextInput::make('deposit_amount')
                        ->label('Deposit Amount')
                        ->numeric()
                        ->prefix('€')
                        ->nullable()
                        ->helperText('Leave blank to use config percentage'),
                    TextInput::make('currency')
                        ->maxLength(3)
                        ->default('EUR'),
                ]),

            Section::make('Status & Notes')
                ->columns(2)
                ->schema([
                    Select::make('status')
                        ->options(WorkshopStatus::class)
                        ->required()
                        ->default(WorkshopStatus::Draft),
                    Textarea::make('notes')
                        ->label('Internal Notes')
                        ->rows(4)
                        ->nullable()
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at')
            ->columns([
                TextColumn::make('course.title')
                    ->label('Course')
                    ->formatStateUsing(fn (mixed $state): string => is_array($state)
                        ? ($state[app()->getLocale()] ?? $state['en'] ?? array_values($state)[0] ?? '')
                        : (string) ($state ?? '—'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->dateTime('d M Y')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->dateTime('d M Y')
                    ->sortable(),
                TextColumn::make('location.name')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('bookings_count')
                    ->label('Bookings')
                    ->counts('bookings')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(WorkshopStatus::class),
                SelectFilter::make('course')
                    ->relationship('course', 'title')
                    ->getOptionLabelFromRecordUsing(fn (Course $record): string => is_array($record->title)
                        ? ($record->title[app()->getLocale()] ?? $record->title['en'] ?? array_values($record->title)[0] ?? '')
                        : (string) $record->title)
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
            ->with(['course', 'location'])
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getRelations(): array
    {
        return [
            TrainersRelationManager::class,
            ItineraryRelationManager::class,
            ExtrasRelationManager::class,
            BookingsRelationManager::class,
            ExpressionsOfInterestRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWorkshops::route('/'),
            'create' => Pages\CreateWorkshop::route('/create'),
            'edit' => Pages\EditWorkshop::route('/{record}/edit'),
        ];
    }
}
