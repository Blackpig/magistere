<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('workshop_id')->constrained()->cascadeOnDelete();
            $table->string('reference')->unique();

            // Primary contact
            $table->string('contact_first_name');
            $table->string('contact_last_name');
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();
            $table->string('contact_organisation')->nullable();

            // Booking state
            $table->string('status')->default('pending');
            $table->string('payment_status')->default('unpaid');
            $table->unsignedInteger('attendee_count')->default(1);

            // Pricing
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->char('currency', 3)->default('EUR');

            // Notes
            $table->text('notes')->nullable();
            $table->text('internal_notes')->nullable();

            // Consent
            $table->boolean('marketing_consent')->default(false);
            $table->boolean('gdpr_consent')->default(false);

            // State timestamps
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('payment_status');
            $table->index('contact_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
