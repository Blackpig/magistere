<?php

namespace BlackpigCreatif\Magistere\Filament\Concerns;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

/**
 * Provides helpers for form fields that read/write translatable JSON columns.
 *
 * Until spatie/laravel-translatable is added as a confirmed dependency, these
 * helpers store and retrieve values keyed by the application locale, e.g.
 * ['en' => 'My Value']. Swap for a proper TranslatableInput when available.
 */
trait HasTranslatableFields
{
    protected static function translatableInput(string $name): TextInput
    {
        return TextInput::make($name)
            ->afterStateHydrated(function (TextInput $component, mixed $state): void {
                if (is_array($state)) {
                    $locale = app()->getLocale();
                    $component->state($state[$locale] ?? $state['en'] ?? array_values($state)[0] ?? '');
                }
            })
            ->dehydrateStateUsing(function (mixed $state): array {
                if (is_array($state)) {
                    return $state;
                }

                return [app()->getLocale() => (string) $state];
            });
    }

    protected static function translatableTextarea(string $name): Textarea
    {
        return Textarea::make($name)
            ->afterStateHydrated(function (Textarea $component, mixed $state): void {
                if (is_array($state)) {
                    $locale = app()->getLocale();
                    $component->state($state[$locale] ?? $state['en'] ?? array_values($state)[0] ?? '');
                }
            })
            ->dehydrateStateUsing(function (mixed $state): array {
                if (is_array($state)) {
                    return $state;
                }

                return [app()->getLocale() => (string) $state];
            });
    }
}
