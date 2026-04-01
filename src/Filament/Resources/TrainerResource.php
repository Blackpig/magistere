<?php

namespace BlackpigCreatif\Magistere\Filament\Resources;

use BlackpigCreatif\Magistere\Enums\TrainerStatus;
use BlackpigCreatif\Magistere\Filament\Concerns\BelongsToMagistereNavigationGroup;
use BlackpigCreatif\Magistere\Filament\Concerns\HasTranslatableFields;
use BlackpigCreatif\Magistere\Filament\Resources\TrainerResource\Pages;
use BlackpigCreatif\Magistere\Models\Trainer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class TrainerResource extends Resource
{
    use BelongsToMagistereNavigationGroup;
    use HasTranslatableFields;

    protected static ?string $model = Trainer::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 40;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profile')
                ->columns(2)
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, ?string $state): void {
                            $set('slug', Str::slug($state ?? ''));
                        }),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(255)
                        ->unique(Trainer::class, 'slug', ignoreRecord: true),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(Trainer::class, 'email', ignoreRecord: true),
                    TextInput::make('phone')
                        ->tel()
                        ->maxLength(50)
                        ->nullable(),
                    TextInput::make('website')
                        ->url()
                        ->maxLength(255)
                        ->nullable(),
                    Select::make('status')
                        ->options(TrainerStatus::class)
                        ->required()
                        ->default(TrainerStatus::Active),
                ]),

            Section::make('Biography')
                ->schema([
                    static::translatableTextarea('bio')
                        ->rows(6)
                        ->nullable(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('workshops_count')
                    ->label('Workshops')
                    ->counts('workshops')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(TrainerStatus::class),
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
            'index' => Pages\ListTrainers::route('/'),
            'create' => Pages\CreateTrainer::route('/create'),
            'edit' => Pages\EditTrainer::route('/{record}/edit'),
        ];
    }
}
