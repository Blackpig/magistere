<?php

namespace BlackpigCreatif\Magistere\Filament\Resources;

use BlackpigCreatif\Magistere\Enums\CourseLevel;
use BlackpigCreatif\Magistere\Enums\CourseStatus;
use BlackpigCreatif\Magistere\Filament\Concerns\BelongsToMagistereNavigationGroup;
use BlackpigCreatif\Magistere\Filament\Concerns\HasTranslatableFields;
use BlackpigCreatif\Magistere\Filament\Resources\CourseResource\Pages;
use BlackpigCreatif\Magistere\Models\Category;
use BlackpigCreatif\Magistere\Models\Course;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class CourseResource extends Resource
{
    use BelongsToMagistereNavigationGroup;
    use HasTranslatableFields;

    protected static ?string $model = Course::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Course Details')
                ->columns(2)
                ->schema([
                    static::translatableInput('title')
                        ->label('Title')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, ?string $state): void {
                            $set('slug', Str::slug($state ?? ''));
                        })
                        ->columnSpanFull(),

                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(Course::class, 'slug', ignoreRecord: true),

                    Select::make('category_id')
                        ->label('Category')
                        ->relationship('category', 'name')
                        ->getOptionLabelFromRecordUsing(fn (Category $record): string => is_array($record->name)
                            ? ($record->name[app()->getLocale()] ?? $record->name['en'] ?? array_values($record->name)[0] ?? '')
                            : (string) $record->name)
                        ->searchable()
                        ->preload()
                        ->nullable(),

                    Select::make('level')
                        ->options(CourseLevel::class)
                        ->required()
                        ->default(CourseLevel::All),

                    Select::make('status')
                        ->options(CourseStatus::class)
                        ->required()
                        ->default(CourseStatus::Draft)
                        ->live(),

                    DateTimePicker::make('published_at')
                        ->nullable()
                        ->visible(fn (Get $get): bool => $get('status') === CourseStatus::Active->value),
                ]),

            Section::make('Description')
                ->schema([
                    static::translatableTextarea('summary')
                        ->label('Summary')
                        ->rows(3)
                        ->helperText('Short description for cards and listings')
                        ->nullable(),

                    static::translatableTextarea('description')
                        ->label('Description')
                        ->rows(8)
                        ->helperText('Full description, shown on the course detail page')
                        ->nullable(),
                ]),

            Section::make('Capacity & Pricing')
                ->columns(3)
                ->schema([
                    TextInput::make('max_capacity')
                        ->label('Max Capacity')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->helperText('Absolute ceiling — workshops can never exceed this'),

                    TextInput::make('min_capacity')
                        ->label('Min Capacity')
                        ->numeric()
                        ->nullable()
                        ->minValue(1),

                    TextInput::make('base_price')
                        ->label('Base Price')
                        ->numeric()
                        ->prefix('€')
                        ->default(0),

                    TextInput::make('currency')
                        ->label('Currency')
                        ->maxLength(3)
                        ->default('EUR'),

                    TextInput::make('duration_days')
                        ->label('Duration (days)')
                        ->numeric()
                        ->nullable()
                        ->minValue(1),

                    TextInput::make('duration_hours')
                        ->label('Duration (hours)')
                        ->numeric()
                        ->nullable()
                        ->minValue(1),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->formatStateUsing(fn (mixed $state): string => is_array($state)
                        ? ($state[app()->getLocale()] ?? $state['en'] ?? array_values($state)[0] ?? '')
                        : (string) $state)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('category.name')
                    ->formatStateUsing(fn (mixed $state): string => is_array($state)
                        ? ($state[app()->getLocale()] ?? $state['en'] ?? array_values($state)[0] ?? '')
                        : (string) ($state ?? '—'))
                    ->sortable()
                    ->placeholder('—'),
                TextColumn::make('level')
                    ->badge()
                    ->sortable(),
                TextColumn::make('max_capacity')
                    ->label('Max')
                    ->sortable(),
                TextColumn::make('base_price')
                    ->money('EUR')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('workshops_count')
                    ->label('Workshops')
                    ->counts('workshops')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Category $record): string => is_array($record->name)
                        ? ($record->name[app()->getLocale()] ?? $record->name['en'] ?? array_values($record->name)[0] ?? '')
                        : (string) $record->name)
                    ->searchable()
                    ->preload(),
                SelectFilter::make('level')
                    ->options(CourseLevel::class),
                SelectFilter::make('status')
                    ->options(CourseStatus::class),
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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
