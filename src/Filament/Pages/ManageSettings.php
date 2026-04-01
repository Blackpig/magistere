<?php

namespace BlackpigCreatif\Magistere\Filament\Pages;

use BlackpigCreatif\Magistere\Filament\Concerns\BelongsToMagistereNavigationGroup;
use BlackpigCreatif\Magistere\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ManageSettings extends Page
{
    use BelongsToMagistereNavigationGroup;

    protected string $view = 'magistere::filament.pages.manage-settings';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?int $navigationSort = 99;

    /** @var array<string, mixed> */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'booking_reference_prefix' => Setting::get('booking.reference_prefix', config('magistere.booking.reference_prefix', 'MAG')),
            'booking_deposit_percentage' => Setting::get('booking.deposit_percentage', config('magistere.booking.deposit_percentage', 25)),
            'booking_token_expiry_hours' => Setting::get('booking.token_expiry_hours', config('magistere.booking.token_expiry_hours', 72)),
            'booking_require_gdpr_consent' => (bool) Setting::get('booking.require_gdpr_consent', config('magistere.booking.require_gdpr_consent', true)),
            'booking_collect_marketing_consent' => (bool) Setting::get('booking.collect_marketing_consent', config('magistere.booking.collect_marketing_consent', true)),
            'feature_eoi' => (bool) Setting::get('features.expressions_of_interest', config('magistere.features.expressions_of_interest', true)),
            'feature_itinerary' => (bool) Setting::get('features.itinerary', config('magistere.features.itinerary', true)),
            'feature_extras' => (bool) Setting::get('features.extras', config('magistere.features.extras', true)),
            'feature_trainers' => (bool) Setting::get('features.trainers', config('magistere.features.trainers', true)),
            'feature_locations' => (bool) Setting::get('features.locations', config('magistere.features.locations', true)),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->statePath('data')
            ->components([
                Form::make([
                    Section::make('Booking')
                        ->columns(3)
                        ->schema([
                            TextInput::make('booking_reference_prefix')
                                ->label('Reference Prefix')
                                ->required()
                                ->maxLength(10)
                                ->helperText('Prepended to booking reference numbers (e.g. MAG-0001)'),
                            TextInput::make('booking_deposit_percentage')
                                ->label('Deposit Percentage')
                                ->numeric()
                                ->required()
                                ->minValue(0)
                                ->maxValue(100)
                                ->suffix('%'),
                            TextInput::make('booking_token_expiry_hours')
                                ->label('EOI Token Expiry')
                                ->numeric()
                                ->required()
                                ->minValue(1)
                                ->suffix('hours')
                                ->helperText('How long booking invitation links remain valid'),
                        ]),

                    Section::make('Consent')
                        ->columns(2)
                        ->schema([
                            Toggle::make('booking_require_gdpr_consent')
                                ->label('Require GDPR Consent')
                                ->helperText('Make GDPR consent mandatory on booking forms'),
                            Toggle::make('booking_collect_marketing_consent')
                                ->label('Collect Marketing Consent')
                                ->helperText('Show marketing opt-in on booking forms'),
                        ]),

                    Section::make('Features')
                        ->columns(2)
                        ->description('Enable or disable optional plugin features. Disabling a feature hides its admin panels but does not remove data.')
                        ->schema([
                            Toggle::make('feature_eoi')
                                ->label('Expressions of Interest'),
                            Toggle::make('feature_itinerary')
                                ->label('Workshop Itinerary'),
                            Toggle::make('feature_extras')
                                ->label('Extras / Add-ons'),
                            Toggle::make('feature_trainers')
                                ->label('Trainers'),
                            Toggle::make('feature_locations')
                                ->label('Locations'),
                        ]),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Save Settings')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::set('booking.reference_prefix', $data['booking_reference_prefix']);
        Setting::set('booking.deposit_percentage', $data['booking_deposit_percentage']);
        Setting::set('booking.token_expiry_hours', $data['booking_token_expiry_hours']);
        Setting::set('booking.require_gdpr_consent', $data['booking_require_gdpr_consent']);
        Setting::set('booking.collect_marketing_consent', $data['booking_collect_marketing_consent']);
        Setting::set('features.expressions_of_interest', $data['feature_eoi']);
        Setting::set('features.itinerary', $data['feature_itinerary']);
        Setting::set('features.extras', $data['feature_extras']);
        Setting::set('features.trainers', $data['feature_trainers']);
        Setting::set('features.locations', $data['feature_locations']);

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }
}
