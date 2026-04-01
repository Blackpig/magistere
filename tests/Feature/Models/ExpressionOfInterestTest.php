<?php

use BlackpigCreatif\Magistere\Database\Factories\CourseFactory;
use BlackpigCreatif\Magistere\Database\Factories\ExpressionOfInterestFactory;
use BlackpigCreatif\Magistere\Database\Factories\WorkshopFactory;

it('generates a token on creation', function (): void {
    $eoi = ExpressionOfInterestFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->create();

    expect($eoi->token)->toBeString()->toHaveLength(64);
});

it('is not expired when token_expires_at is null', function (): void {
    $eoi = ExpressionOfInterestFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->create(['token_expires_at' => null]);

    expect($eoi->isTokenExpired())->toBeFalse();
});

it('is expired when token_expires_at is in the past', function (): void {
    $eoi = ExpressionOfInterestFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->expired()
        ->create();

    expect($eoi->isTokenExpired())->toBeTrue();
});

it('is not expired when token_expires_at is in the future', function (): void {
    $eoi = ExpressionOfInterestFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->create(['token_expires_at' => now()->addHours(24)]);

    expect($eoi->isTokenExpired())->toBeFalse();
});

it('refresh token issues a new token and resets expiry', function (): void {
    $eoi = ExpressionOfInterestFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->expired()
        ->create();

    $oldToken = $eoi->token;

    $eoi->refreshToken();
    $eoi->refresh();

    expect($eoi->token)->not->toBe($oldToken);
    expect($eoi->isTokenExpired())->toBeFalse();
    expect($eoi->token_expires_at)->not->toBeNull();
});

it('full name accessor combines first and last name', function (): void {
    $eoi = ExpressionOfInterestFactory::new()
        ->for(WorkshopFactory::new()->for(CourseFactory::new()))
        ->create([
            'first_name' => 'Alice',
            'last_name' => 'Dupont',
        ]);

    expect($eoi->full_name)->toBe('Alice Dupont');
});

it('notifiable scope returns only new interest EOIs', function (): void {
    $workshop = WorkshopFactory::new()->for(CourseFactory::new())->create();

    ExpressionOfInterestFactory::new()->for($workshop)->asNew()->interest()->create();
    ExpressionOfInterestFactory::new()->for($workshop)->waitlist()->create();
    ExpressionOfInterestFactory::new()->for($workshop)->converted()->create();

    $notifiable = \BlackpigCreatif\Magistere\Models\ExpressionOfInterest::notifiable()->get();

    expect($notifiable)->toHaveCount(1);
});
