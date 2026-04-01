<?php

return [

    'currency' => 'EUR',

    'per_page' => 12,

    'route_prefix' => 'magistere',

    'route_middleware' => ['web'],

    'features' => [
        'expressions_of_interest' => true,
        'itinerary' => true,
        'extras' => true,
        'trainers' => true,
        'locations' => true,
    ],

    'booking' => [
        'reference_prefix' => 'MAG',
        'require_gdpr_consent' => true,
        'collect_marketing_consent' => true,
        'deposit_percentage' => 25,
        'payment_methods' => ['bank_transfer', 'cash', 'cheque', 'card_manual', 'other'],
        'token_expiry_hours' => 72,
    ],

    /*
    |--------------------------------------------------------------------------
    | Category Colour Palette
    |--------------------------------------------------------------------------
    | Constrained palette used by CategoryResource's colour picker.
    | Limiting choices ensures design consistency across calendar and listings.
    */
    'category_colours' => [
        '#e63946',
        '#2a9d8f',
        '#e9c46a',
        '#457b9d',
        '#a8dadc',
        '#f4a261',
        '#264653',
        '#e76f51',
    ],

    /*
    |--------------------------------------------------------------------------
    | Ephemeride Integration
    |--------------------------------------------------------------------------
    | Statuses included when building calendar events via WorkshopProvider.
    | Add 'completed' to show historical workshops in the calendar.
    */
    'ephemeride' => [
        'provider' => \BlackpigCreatif\Magistere\Ephemeride\WorkshopProvider::class,
        'workshop_statuses' => ['published', 'confirmed'],
    ],

];
