<div class="magistere-booking-form">

    {{-- Token expired --}}
    @if ($tokenExpired)
        <div class="magistere-booking-form__alert magistere-booking-form__alert--error" role="alert">
            <h3>{{ __('magistere::booking.token_expired_heading') }}</h3>
            <p>{{ __('magistere::booking.token_expired_body') }}</p>
        </div>

    {{-- Confirmation screen --}}
    @elseif ($submitted && $booking)
        <div class="magistere-booking-form__confirmation" role="status">
            <h2>{{ __('magistere::booking.confirmed_heading') }}</h2>
            <p>{{ __('magistere::booking.confirmed_intro') }}</p>

            <dl class="magistere-booking-form__summary">
                <dt>{{ __('magistere::booking.reference') }}</dt>
                <dd>{{ $booking->reference }}</dd>

                <dt>{{ __('magistere::booking.workshop') }}</dt>
                <dd>{{ $workshop->starts_at->format('d M Y') }}</dd>

                <dt>{{ __('magistere::booking.attendees') }}</dt>
                <dd>{{ $booking->attendee_count }}</dd>

                <dt>{{ __('magistere::booking.total') }}</dt>
                <dd>{{ number_format((float) $booking->subtotal, 2) }} {{ $booking->currency }}</dd>
            </dl>

            <div class="magistere-booking-form__payment-note">
                <h3>{{ __('magistere::booking.payment_heading') }}</h3>
                <p>{{ __('magistere::booking.payment_note') }}</p>
            </div>
        </div>

    {{-- Booking form --}}
    @else
        <form wire:submit="submit" novalidate>

            {{-- ── Contact Details ── --}}
            <section class="magistere-form-section">
                <h2 class="magistere-form-section__heading">{{ __('magistere::booking.contact_heading') }}</h2>

                <div class="magistere-field-row">
                    <div class="magistere-field">
                        <label for="contact-first-name">{{ __('magistere::booking.first_name') }}</label>
                        <input id="contact-first-name" type="text" wire:model="contact.first_name" autocomplete="given-name" required>
                        @error('contact.first_name') <span class="magistere-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="magistere-field">
                        <label for="contact-last-name">{{ __('magistere::booking.last_name') }}</label>
                        <input id="contact-last-name" type="text" wire:model="contact.last_name" autocomplete="family-name" required>
                        @error('contact.last_name') <span class="magistere-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="magistere-field-row">
                    <div class="magistere-field">
                        <label for="contact-email">{{ __('magistere::booking.email') }}</label>
                        <input id="contact-email" type="email" wire:model="contact.email" autocomplete="email" required>
                        @error('contact.email') <span class="magistere-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="magistere-field">
                        <label for="contact-phone">{{ __('magistere::booking.phone') }}</label>
                        <input id="contact-phone" type="tel" wire:model="contact.phone" autocomplete="tel">
                        @error('contact.phone') <span class="magistere-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="magistere-field">
                    <label for="contact-organisation">{{ __('magistere::booking.organisation') }}</label>
                    <input id="contact-organisation" type="text" wire:model="contact.organisation" autocomplete="organization">
                    @error('contact.organisation') <span class="magistere-error">{{ $message }}</span> @enderror
                </div>

                <div class="magistere-field">
                    <label for="contact-notes">{{ __('magistere::booking.notes') }}</label>
                    <textarea id="contact-notes" wire:model="contact.notes" rows="3"></textarea>
                    @error('contact.notes') <span class="magistere-error">{{ $message }}</span> @enderror
                </div>
            </section>

            {{-- ── Attendees ── --}}
            <section class="magistere-form-section">
                <h2 class="magistere-form-section__heading">{{ __('magistere::booking.attendees_heading') }}</h2>
                <p class="magistere-form-section__hint">{{ __('magistere::booking.attendees_hint') }}</p>

                @foreach ($attendees as $i => $attendee)
                    <fieldset class="magistere-attendee-row" wire:key="attendee-{{ $i }}">
                        <legend>{{ __('magistere::booking.attendee_n', ['n' => $i + 1]) }}</legend>

                        <div class="magistere-field-row">
                            <div class="magistere-field">
                                <label>{{ __('magistere::booking.first_name') }}</label>
                                <input type="text" wire:model="attendees.{{ $i }}.first_name" required>
                                @error("attendees.{$i}.first_name") <span class="magistere-error">{{ $message }}</span> @enderror
                            </div>
                            <div class="magistere-field">
                                <label>{{ __('magistere::booking.last_name') }}</label>
                                <input type="text" wire:model="attendees.{{ $i }}.last_name" required>
                                @error("attendees.{$i}.last_name") <span class="magistere-error">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <div class="magistere-field">
                            <label>{{ __('magistere::booking.email') }}</label>
                            <input type="email" wire:model="attendees.{{ $i }}.email">
                            @error("attendees.{$i}.email") <span class="magistere-error">{{ $message }}</span> @enderror
                        </div>

                        <div class="magistere-field-row">
                            <div class="magistere-field">
                                <label>{{ __('magistere::booking.dietary') }}</label>
                                <textarea wire:model="attendees.{{ $i }}.dietary_requirements" rows="2"></textarea>
                            </div>
                            <div class="magistere-field">
                                <label>{{ __('magistere::booking.accessibility') }}</label>
                                <textarea wire:model="attendees.{{ $i }}.accessibility_requirements" rows="2"></textarea>
                            </div>
                        </div>

                        @if ($loop->index > 0)
                            <button
                                type="button"
                                wire:click="removeAttendee({{ $i }})"
                                class="magistere-btn magistere-btn--ghost magistere-btn--sm"
                            >{{ __('magistere::booking.remove_attendee') }}</button>
                        @endif
                    </fieldset>
                @endforeach

                <button
                    type="button"
                    wire:click="addAttendee"
                    class="magistere-btn magistere-btn--secondary magistere-btn--sm"
                >+ {{ __('magistere::booking.add_attendee') }}</button>
            </section>

            {{-- ── Extras ── --}}
            @if ($extras->isNotEmpty())
                <section class="magistere-form-section">
                    <h2 class="magistere-form-section__heading">{{ __('magistere::booking.extras_heading') }}</h2>

                    @foreach ($extras as $extra)
                        @php
                            $locale = app()->getLocale();
                            $extraTitle = is_array($extra->title)
                                ? ($extra->title[$locale] ?? $extra->title['en'] ?? array_values($extra->title)[0] ?? '')
                                : (string) $extra->title;
                        @endphp
                        <div class="magistere-extra-row" wire:key="extra-{{ $extra->id }}">
                            <div class="magistere-extra-row__info">
                                <span class="magistere-extra-row__title">{{ $extraTitle }}</span>
                                @if ($extra->price > 0)
                                    <span class="magistere-extra-row__price">
                                        +{{ number_format((float) $extra->price, 2) }}
                                        @if ($extra->per === \BlackpigCreatif\Magistere\Enums\ExtraPer::Attendee)
                                            {{ __('magistere::booking.per_attendee') }}
                                        @endif
                                    </span>
                                @endif
                            </div>

                            @if ($extra->is_required)
                                <span class="magistere-extra-row__required">{{ __('magistere::booking.included') }}</span>
                            @elseif ($extra->per === \BlackpigCreatif\Magistere\Enums\ExtraPer::Attendee)
                                <label class="magistere-toggle">
                                    <input
                                        type="checkbox"
                                        wire:model="selectedExtras.{{ $extra->id }}"
                                        true-value="1"
                                        false-value="0"
                                    >
                                    <span>{{ __('magistere::booking.add') }}</span>
                                </label>
                            @else
                                <input
                                    type="number"
                                    wire:model="selectedExtras.{{ $extra->id }}"
                                    min="0"
                                    class="magistere-extra-row__qty"
                                >
                            @endif
                        </div>
                    @endforeach
                </section>
            @endif

            {{-- ── Review & Consent ── --}}
            <section class="magistere-form-section">
                <h2 class="magistere-form-section__heading">{{ __('magistere::booking.review_heading') }}</h2>

                <div class="magistere-booking-form__totals">
                    <span>{{ count($attendees) }} {{ trans_choice('magistere::booking.attendee_count', count($attendees)) }}</span>
                    <strong>{{ number_format($this->subtotal, 2) }} {{ $workshop->currency ?? 'EUR' }}</strong>
                </div>

                @if (config('magistere.booking.require_gdpr_consent', true))
                    <div class="magistere-field magistere-field--checkbox">
                        <label>
                            <input type="checkbox" wire:model="contact.gdpr_consent" required>
                            {!! __('magistere::booking.gdpr_consent') !!}
                        </label>
                        @error('contact.gdpr_consent') <span class="magistere-error">{{ $message }}</span> @enderror
                    </div>
                @endif

                @if (config('magistere.booking.collect_marketing_consent', true))
                    <div class="magistere-field magistere-field--checkbox">
                        <label>
                            <input type="checkbox" wire:model="contact.marketing_consent">
                            {{ __('magistere::booking.marketing_consent') }}
                        </label>
                    </div>
                @endif
            </section>

            <button type="submit" wire:loading.attr="disabled" class="magistere-btn magistere-btn--primary">
                <span wire:loading.remove>{{ __('magistere::booking.submit') }}</span>
                <span wire:loading>{{ __('magistere::booking.submitting') }}</span>
            </button>

        </form>
    @endif
</div>
