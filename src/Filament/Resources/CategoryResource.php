<?php

namespace BlackpigCreatif\Magistere\Filament\Resources;

use BlackpigCreatif\Magistere\Enums\CategoryStatus;
use BlackpigCreatif\Magistere\Filament\Concerns\BelongsToMagistereNavigationGroup;
use BlackpigCreatif\Magistere\Filament\Concerns\HasTranslatableFields;
use BlackpigCreatif\Magistere\Filament\Resources\CategoryResource\Pages;
use BlackpigCreatif\Magistere\Models\Category;
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
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    use BelongsToMagistereNavigationGroup;
    use HasTranslatableFields;

    protected static ?string $model = Category::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    protected static ?int $navigationSort = 60;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columns(2)
                ->schema([
                    static::translatableInput('name')
                        ->label('Name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (\Filament\Schemas\Components\Utilities\Set $set, ?string $state): void {
                            $set('slug', Str::slug($state ?? ''));
                        })
                        ->columnSpanFull(),

                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(Category::class, 'slug', ignoreRecord: true),

                    Select::make('colour')
                        ->label('Colour')
                        ->required()
                        ->options(static::colourOptions())
                        ->allowHtml(),

                    TextInput::make('sort_order')
                        ->numeric()
                        ->default(0)
                        ->minValue(0),

                    Select::make('status')
                        ->options(CategoryStatus::class)
                        ->required()
                        ->default(CategoryStatus::Active),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ColorColumn::make('colour')
                    ->label('')
                    ->width('40px'),
                TextColumn::make('name')
                    ->formatStateUsing(fn (mixed $state): string => is_array($state)
                        ? ($state[app()->getLocale()] ?? $state['en'] ?? array_values($state)[0] ?? '')
                        : (string) $state)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('courses_count')
                    ->label('Courses')
                    ->counts('courses')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(CategoryStatus::class),
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }

    /** @return array<string, string> */
    protected static function colourOptions(): array
    {
        $palette = config('magistere.category_colours', [
            '#e63946', '#2a9d8f', '#e9c46a', '#457b9d',
            '#a8dadc', '#f4a261', '#264653', '#e76f51',
        ]);

        $options = [];

        foreach ($palette as $hex) {
            $options[$hex] = '<span style="display:inline-flex;align-items:center;gap:8px;">'
                . '<span style="display:inline-block;width:16px;height:16px;border-radius:50%;background:' . $hex . ';border:1px solid rgba(0,0,0,.1);"></span>'
                . $hex
                . '</span>';
        }

        return $options;
    }
}
