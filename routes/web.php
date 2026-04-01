<?php

use BlackpigCreatif\Magistere\Http\Controllers\WorkshopController;
use Illuminate\Support\Facades\Route;

Route::prefix(config('magistere.route_prefix', 'magistere'))
    ->middleware(config('magistere.route_middleware', ['web']))
    ->name('magistere.')
    ->group(function (): void {
        Route::get('/', [WorkshopController::class, 'index'])
            ->name('workshops.index');

        Route::get('/{workshop:slug}', [WorkshopController::class, 'show'])
            ->name('workshops.show');

        Route::get('/{workshop:slug}/book/{token}', [WorkshopController::class, 'bookWithToken'])
            ->name('workshops.book.token');

        Route::get('/{workshop:slug}/interest', [WorkshopController::class, 'interest'])
            ->name('workshops.interest');
    });
