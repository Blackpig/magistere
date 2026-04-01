<div class="magistere-eoi-form">
    @if ($submitted)
        <div class="magistere-eoi-form__success" role="alert">
            <h3>{{ __('magistere::eoi.thank_you_heading') }}</h3>
            <p>{{ __('magistere::eoi.thank_you_body') }}</p>
        </div>
    @else
        <form wire:submit="submit" novalidate>
            {{-- Name row --}}
            <div class="magistere-field-row">
                <div class="magistere-field">
                    <label for="eoi-first-name">{{ __('magistere::eoi.first_name') }}</label>
                    <input
                        id="eoi-first-name"
                        type="text"
                        wire:model="firstName"
                        autocomplete="given-name"
                        required
                    >
                    @error('firstName') <span class="magistere-error">{{ $message }}</span> @enderror
                </div>
                <div class="magistere-field">
                    <label for="eoi-last-name">{{ __('magistere::eoi.last_name') }}</label>
                    <input
                        id="eoi-last-name"
                        type="text"
                        wire:model="lastName"
                        autocomplete="family-name"
                        required
                    >
                    @error('lastName') <span class="magistere-error">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Email & Phone --}}
            <div class="magistere-field-row">
                <div class="magistere-field">
                    <label for="eoi-email">{{ __('magistere::eoi.email') }}</label>
                    <input
                        id="eoi-email"
                        type="email"
                        wire:model="email"
                        autocomplete="email"
                        required
                    >
                    @error('email') <span class="magistere-error">{{ $message }}</span> @enderror
                </div>
                <div class="magistere-field">
                    <label for="eoi-phone">{{ __('magistere::eoi.phone') }}</label>
                    <input
                        id="eoi-phone"
                        type="tel"
                        wire:model="phone"
                        autocomplete="tel"
                    >
                    @error('phone') <span class="magistere-error">{{ $message }}</span> @enderror
                </div>
            </div>

            {{-- Attendee count --}}
            <div class="magistere-field">
                <label for="eoi-attendee-count">{{ __('magistere::eoi.attendee_count') }}</label>
                <input
                    id="eoi-attendee-count"
                    type="number"
                    wire:model="attendeeCount"
                    min="1"
                    required
                >
                @error('attendeeCount') <span class="magistere-error">{{ $message }}</span> @enderror
            </div>

            {{-- Optional message --}}
            <div class="magistere-field">
                <label for="eoi-message">{{ __('magistere::eoi.message') }}</label>
                <textarea
                    id="eoi-message"
                    wire:model="message"
                    rows="3"
                ></textarea>
                @error('message') <span class="magistere-error">{{ $message }}</span> @enderror
            </div>

            <button type="submit" wire:loading.attr="disabled" class="magistere-btn magistere-btn--primary">
                <span wire:loading.remove>{{ __('magistere::eoi.submit') }}</span>
                <span wire:loading>{{ __('magistere::eoi.submitting') }}</span>
            </button>
        </form>
    @endif
</div>
