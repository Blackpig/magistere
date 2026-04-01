# CC Prompt Template — Filament Blueprint Plan

> Standard prompt for kicking off a new feature build. Fill in the `[REQUIREMENTS DOC]` section and paste the contents of the requirements markdown. Everything else is boilerplate.

---

## The Prompt

```
You are a senior PHP/Laravel/Filament developer. Before writing any code, enter **plan mode** and produce a **Filament Blueprint** implementation plan.

## Stack
- PHP 8.4 — use modern conventions throughout: constructor property promotion, readonly properties, named arguments, match expressions, enums with TitleCase keys, union/intersection types, and PHP attributes where appropriate
- Laravel 12
- Filament v5
- Livewire 4
- Tailwind CSS v4
- Pest v4 for tests

## Before Planning
1. Read `/vendor/filament/blueprint/resources/markdown/planning/overview.md` for the required plan format
2. Use `search-docs` for any Filament v5 patterns you need to verify before committing to an approach
3. Use `database-schema` to inspect existing tables relevant to this feature before defining migrations
4. Use `list-artisan-commands` to confirm available `make:filament-*` commands

## Code Conventions (from CLAUDE.md)
- Follow all existing code conventions — check sibling files before creating anything new
- Use `php artisan make:` commands for all generated files, passing `--no-interaction`
- Filament namespaces: form fields → `Filament\Forms\Components\`, infolist entries → `Filament\Infolists\Components\`, layout/schema components → `Filament\Schemas\Components\`, actions → `Filament\Actions\`
- No empty constructors; use constructor property promotion
- Explicit return types on all methods
- Enums: TitleCase keys
- PHPDoc blocks over inline comments
- Run `vendor/bin/pint --dirty --format agent` after any PHP changes
- Every change must have a Pest test

## DRY Principles & Traits

Before implementing, identify any functionality that is shared across two or more models or resources and extract it into a trait rather than duplicating it.

**Model traits** live in `app/Models/Concerns/` and are named `HasWhateverItConcerns` — e.g. `HasActiveStatus`, `HasRenewalDate`, `HasCredentials`. They encapsulate shared scopes, casts, relationships, and accessors.

**Filament traits** live in `app/Filament/Concerns/` and are named with the same `HasWhateverItConcerns` convention — e.g. `HasStatusFilter`, `HasRenewalDateColumn`, `HasVaultUrlField`. They encapsulate shared form fields, table columns, filters, and infolist entries that would otherwise be copy-pasted across multiple resources.

During planning, explicitly list:
- Which traits will be created
- What each trait encapsulates
- Which models / resources use each trait

Do not duplicate logic across models or resources when a trait would serve. When in doubt, extract.

## Requirements

# Magistère — Planning Document
**Package:** `blackpig-creatif/magistere`  
**Version target:** v1.0  
**Stack:** Laravel 12+, Filament v5, Livewire 4+, PHP 8.3+

---

## 0. Instructions for Claude Code

### Stage 1 — Filament Blueprint (do this first, before any implementation)

Use Laravel Boost to produce a Filament Blueprint for `blackpig-creatif/magistere`. The package is a courses and events management system with the following capabilities:

- Manage course templates (Course) and scheduled instances (Workshop)
- Manage locations, trainers, and categories
- Handle bookings with primary contact and multiple attendees
- Record manual payments against bookings (ledger model)
- Capture expressions of interest (interest pre-booking and waitlist)
- Tokenised booking invitation flow from EOI to confirmed booking
- Integrate with `blackpig-creatif/ephemeride` for calendar display
- Integrate with `blackpig-creatif/atelier` for content blocks (optional)
- Package settings management via a bespoke Filament page

The Blueprint should:
- Describe the primary user flows end to end (creating a course, scheduling a workshop, confirming a workshop, notifying interest list, converting EOI to booking, recording a payment, managing the waitlist)
- Map each domain concept and flow to concrete Filament primitives (Resources, Relation Managers, Pages, Actions, Plugins, Widgets)
- Identify all state transitions (Workshop status, Booking status, EOI status, Payment status) and the Actions that trigger them
- Confirm correct Filament v5 namespaces, plugin registration patterns, and relation manager conventions

### Stage 2 — Implementation

Only after the Blueprint has been produced and reviewed should implementation begin. Follow the build order in Section 13. Reference this PLANNING.md throughout the build.

---

## 1. Overview

Magistère is a Filament v5 package for managing training courses, workshops, retreats, and events. It provides the admin panel (headless), an Ephemeride provider for calendar integration, optional Atelier content blocks, and thin front-end Blade templates as a starting point.

**Out of scope for v1:** online payment processing, basket/checkout (deferred to a future package), online/virtual event links, certificates, recurring workshops (RRULE), cascading waitlist automation, auto-notify on workshop confirm.

**In scope for v1:** manual payment recording against bookings (bank transfer, cash, cheque etc), derived payment status from ledger, tokenised EOI booking invitation flow, manual waitlist management.

---

## 2. Core Concepts

| Concept | Description |
|---|---|
| **Category** | Course category with colour — drives calendar colour coding |
| **Course** | The *what* — metadata, description, template for a repeatable event |
| **Workshop** | The *when* — a specific scheduled instance of a Course |
| **Location** | Where a Workshop is held |
| **Trainer** | Who delivers a Workshop (many-to-many with Workshop) |
| **ItineraryItem** | Optional day/session schedule attached to a Workshop |
| **Extra** | Optional add-on (massage, spa, accommodation etc) available on a Workshop |
| **Booking** | A reservation made by a primary contact for one or more attendees |
| **Attendee** | An individual attending under a Booking |
| **Payment** | A manually recorded payment entry against a Booking |
| **ExpressionOfInterest** | Pre-booking interest capture (interest or waitlist) |
| **Setting** | Key/value configuration stored in database, managed via Filament settings page |

---

## 3. Data Model

### 3.1 Category

Manages course categories. Drives calendar colour coding via Ephemeride integration.

```
categories
  id
  name (translatable)
  slug
  colour                          — hex value from config-defined palette (Filament ColorPicker, constrained)
  sort_order
  status                          — enum: active | inactive
  timestamps
  soft_deletes
```

**Relationships:**
- `hasMany` Course

---

### 3.2 Course

The template. Defines what the course *is*, not when it runs.

```
courses
  id
  category_id                     — belongs to Category (nullable)
  title (translatable)
  slug
  summary (translatable)          — short, for cards/listings
  description (translatable)      — long, rich text
  level                           — enum: beginner | intermediate | advanced | all
  duration_days                   — indicative duration (informational)
  duration_hours                  — indicative hours
  min_capacity                    — minimum viable attendees
  max_capacity                    — ABSOLUTE CEILING — can never be exceeded by Workshop or Location
  base_price                      — indicative price placeholder
  currency                        — ISO 4217, default from config (EUR)
  featured_image                  — via ChambreNoir
  gallery                         — via ChambreNoir (array/collection)
  meta (json)                     — SEO via Sceau, extraAttributes passthrough
  status                          — enum: draft | active | archived
  published_at
  timestamps
  soft_deletes
```

**Relationships:**
- `belongsTo` Category
- `hasMany` Workshop

---

### 3.3 Workshop

A specific scheduled instance of a Course.

```
workshops
  id
  course_id                       — belongs to Course
  location_id                     — belongs to Location (nullable)
  title (translatable)            — override course title if needed
  slug
  summary (translatable)          — override if needed
  description (translatable)      — override if needed
  starts_at                       — datetime
  ends_at                         — datetime
  registration_opens_at           — when bookings can be made
  registration_closes_at          — booking deadline
  min_capacity                    — overrides course default if set
  max_capacity                    — workshop-specific override; effective capacity computed from all three sources
  price                           — overrides course base_price if set
  deposit_amount                  — nullable, fixed deposit; falls back to config percentage if null
  currency
  status                          — enum: draft | published | confirmed | cancelled | completed
  featured_image                  — via ChambreNoir
  meta (json)
  notes                           — internal admin notes
  timestamps
  soft_deletes
```

**Relationships:**
- `belongsTo` Course
- `belongsTo` Location
- `belongsToMany` Trainer (pivot: `trainer_workshop`)
- `hasMany` ItineraryItem
- `hasMany` Extra
- `hasMany` Booking
- `hasMany` ExpressionOfInterest

**Computed:**
- `effective_capacity` — `min(course.max_capacity, location.max_capacity ?? ∞, workshop.max_capacity ?? ∞)`. Course ceiling is absolute and can never be exceeded.
- `attendees_count` — sum of attendees across confirmed bookings
- `available_spaces` — effective_capacity minus attendees_count
- `is_full` — available_spaces <= 0
- `is_open_for_booking` — status published/confirmed + within registration window + not full

**Filament hint:** WorkshopResource form should surface capacity constraints clearly, e.g. *"Course max: 8 · Location max: 6 · Effective: 6"*

**Status flow:**
```
draft → published → confirmed → completed
                 ↘ cancelled
Any status → cancelled
```

---

### 3.4 Location

```
locations
  id
  name
  address_line_1
  address_line_2
  city
  region
  postcode
  country                         — ISO 3166-1 alpha-2
  lat                             — decimal, nullable
  lng                             — decimal, nullable
  max_capacity                    — nullable, venue physical limit (cannot exceed Course ceiling)
  description (translatable)      — nullable, for front end
  website                         — nullable
  featured_image                  — via ChambreNoir
  meta (json)
  timestamps
  soft_deletes
```

---

### 3.5 Trainer

```
trainers
  id
  name
  slug
  bio (translatable)
  email
  phone                           — nullable
  website                         — nullable
  featured_image                  — via ChambreNoir (headshot/profile)
  gallery                         — via ChambreNoir
  meta (json)
  status                          — enum: active | inactive
  timestamps
  soft_deletes
```

**Relationships:**
- `belongsToMany` Workshop (pivot: `trainer_workshop`)

Pivot table `trainer_workshop`:
```
trainer_id
workshop_id
role                              — nullable, e.g. "lead", "assistant"
sort_order
```

---

### 3.6 ItineraryItem

Optional structured schedule for a Workshop.

```
itinerary_items
  id
  workshop_id
  day                             — integer, day number (1, 2, 3...)
  start_time                      — time, nullable
  end_time                        — time, nullable
  title (translatable)
  description (translatable)      — nullable
  sort_order
  timestamps
```

---

### 3.7 Extra

Optional add-ons available for a Workshop.

```
extras
  id
  workshop_id
  title (translatable)
  description (translatable)      — nullable
  price                           — decimal, 0 for free extras
  currency
  capacity                        — nullable, unlimited if null
  per                             — enum: booking | attendee
  is_required                     — boolean, default false
  sort_order
  status                          — enum: active | inactive
  timestamps
  soft_deletes
```

**Relationships:**
- `belongsTo` Workshop
- `belongsToMany` Booking (via `booking_extra`)
- `belongsToMany` Attendee (via `attendee_extra` — for per-attendee extras)

---

### 3.8 Booking

The reservation entity. Made by a primary contact on behalf of one or more attendees.

```
bookings
  id
  workshop_id
  reference                       — unique human-readable ref (e.g. MAG-2026-0042)

  — Primary contact
  contact_first_name
  contact_last_name
  contact_email
  contact_phone                   — nullable
  contact_organisation            — nullable

  — Booking state
  status                          — enum: pending | confirmed | waitlisted | cancelled | completed | no_show
  payment_status                  — derived: unpaid | deposit_received | part_paid | paid | overpaid | refunded
  attendee_count                  — denormalised count

  — Pricing
  subtotal                        — decimal, expected total
  amount_paid                     — decimal, denormalised running total from payment ledger
  currency

  — Notes
  notes                           — customer notes/special requirements
  internal_notes                  — admin only

  — Consent
  marketing_consent               — boolean
  gdpr_consent                    — boolean

  — Timestamps
  confirmed_at                    — nullable
  cancelled_at                    — nullable
  completed_at                    — nullable
  timestamps
  soft_deletes
```

**Status flow:**
```
pending → confirmed → completed
        ↘ waitlisted → confirmed (if space opens)
Any status → cancelled
confirmed  → no_show
```

**Payment status** derived from ledger:
- `unpaid` — no payments recorded
- `deposit_received` — payments >= deposit threshold
- `part_paid` — some received, below deposit threshold
- `paid` — amount_paid >= subtotal
- `overpaid` — amount_paid > subtotal (flag for admin)
- `refunded` — net payments returned

**Relationships:**
- `belongsTo` Workshop
- `hasMany` Attendee
- `hasMany` Payment
- `belongsToMany` Extra (via `booking_extra`)

---

### 3.9 Attendee

```
attendees
  id
  booking_id
  first_name
  last_name
  email                           — nullable
  phone                           — nullable
  dietary_requirements            — nullable
  accessibility_requirements      — nullable
  notes                           — nullable
  is_primary_contact              — boolean
  checked_in_at                   — nullable
  timestamps
  soft_deletes
```

**Relationships:**
- `belongsTo` Booking
- `belongsToMany` Extra (via `attendee_extra`)

---

### 3.10 Payment

Manually recorded ledger entries against a Booking.

```
payments
  id
  booking_id
  attendee_id                     — nullable, if attributed to specific attendee

  amount                          — decimal
  currency                        — ISO 4217

  method                          — enum: bank_transfer | cash | cheque | card_manual | other
  reference                       — nullable (bank transfer ref, cheque number etc)

  paid_at                         — date of payment
  notes                           — nullable

  type                            — enum: payment | refund | adjustment

  recorded_by                     — nullable, FK to users
  timestamps
  soft_deletes
```

**Logic:**
- `booking.amount_paid` = sum of `payment` entries minus `refund` entries
- `booking.payment_status` derived from `amount_paid` vs `subtotal` and deposit threshold
- Deposit threshold: per-workshop `deposit_amount` field, falls back to config percentage

---

### 3.11 ExpressionOfInterest

Pre-booking interest capture. Two modes: interest (unconfirmed workshop) and waitlist (full workshop).

```
expressions_of_interest
  id
  workshop_id                     — nullable (course-level interest if no workshop yet)
  course_id                       — nullable (interest in course generally)
  first_name
  last_name
  email
  phone                           — nullable
  attendee_count
  message                         — nullable
  source                          — enum: interest | waitlist
  status                          — enum: new | contacted | converted | archived
  converted_booking_id            — nullable, FK to bookings
  token                           — unique, generated on creation
  token_expires_at                — nullable, set when notification sent
  notified_at                     — nullable
  timestamps
  soft_deletes
```

**EOI flows:**

*Interest flow (unconfirmed workshop needs minimum numbers):*
```
Workshop unconfirmed
  → Front end shows "Register Interest" CTA
    → EOI created (source: interest)
      → Admin confirms workshop
        → Admin triggers "Notify Interest List" bulk action
          → Tokenised email sent (EoiBookingInvitationNotification)
            → Contact clicks link → pre-populated booking form
              → Booking created (status: pending)
```

*Waitlist flow (workshop full):*
```
Workshop full
  → Front end shows "Join Waitlist" CTA
    → EOI created (source: waitlist)
      → Booking cancelled, space opens
        → Admin reviews waitlist, hits "Offer Place" on chosen EOI
          → Tokenised email sent
            → Contact clicks link → pre-populated booking form
              → Booking created (status: pending)
```

*Token expiry:* configurable, default 72 hours. Expired links show friendly message with link to normal booking form.

---

### 3.12 Setting

Key/value configuration stored in database. Managed via bespoke Filament settings page.

```
settings
  id
  key                             — unique string e.g. 'booking.reference_prefix'
  value                           — text, cast as needed per key
  timestamps
```

---

## 4. Filament Resources

### Full Resources

| Resource | Notes |
|---|---|
| `CategoryResource` | CRUD, colour picker constrained to config palette, translatable name |
| `CourseResource` | Full CRUD, ChambreNoir media, translatable fields |
| `WorkshopResource` | Full CRUD, relation managers for Trainers, Itinerary, Extras, Bookings, EOIs |
| `BookingResource` | Full CRUD, relation managers for Attendees, Extras, Payments |
| `LocationResource` | Full CRUD |
| `TrainerResource` | Full CRUD |

### Bespoke Pages

| Page | Notes |
|---|---|
| `ManageSettings` | Settings key/value management — typed fields per setting key, not raw table |

### Relation Managers

| Manager | Parent | Notes |
|---|---|---|
| `TrainersRelationManager` | Workshop | Attach/detach, role and sort order |
| `ItineraryRelationManager` | Workshop | Ordered itinerary items |
| `ExtrasRelationManager` | Workshop | Manage available extras |
| `BookingsRelationManager` | Workshop | View/manage bookings from workshop context |
| `ExpressionsOfInterestRelationManager` | Workshop | EOI list, convert and offer actions |
| `AttendeesRelationManager` | Booking | Manage attendees |
| `PaymentsRelationManager` | Booking | Payment ledger — running total, balance remaining |

### Key Filament Actions

| Action | Context | Notes |
|---|---|---|
| *Confirm* | Workshop | Transition to confirmed, trigger notification stub |
| *Cancel* | Workshop | Transition to cancelled, notify confirmed bookings |
| *Duplicate* | Workshop | Clone with new dates, preserves trainers/itinerary/extras |
| *Notify Interest List* | Workshop EOI relation manager (bulk) | Sends tokenised email to all interest EOIs with status: new |
| *Offer Place* | Individual EOI (waitlist) | Sends tokenised email to chosen waitlist EOI |
| *Re-send Notification* | Individual EOI | Regenerates token, resets expiry, resends |
| *Convert to Booking* | Individual EOI | Creates pending booking pre-populated from EOI |
| *Confirm* | Booking | Pending → confirmed |
| *Move to Waitlist* | Booking | If workshop full |
| *Check In* | Attendee | Sets checked_in_at |

### Filament Widgets (lower priority, v1 roadmap)

- `UpcomingWorkshopsWidget` — workshops in next 30/60/90 days
- `BookingStatsWidget` — confirmed bookings, available spaces, waitlist count
- `CalendarWidget` — Ephemeride component embedded in panel (requires Ephemeride)

---

## 5. Ephemeride Integration

Magistère ships a ready-made Ephemeride provider.

### Contract

```php
namespace BlackpigCreatif\Magistere\Ephemeride;

use BlackpigCreatif\Ephemeride\Contracts\ProvidesEphemerides;
use BlackpigCreatif\Ephemeride\Data\EphemerisEvent;
use BlackpigCreatif\Magistere\Models\Workshop;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class WorkshopProvider implements ProvidesEphemerides
{
    public function getEphemerides(Carbon $from, Carbon $to): Collection
    {
        return Workshop::query()
            ->whereIn('status', config('magistere.ephemeride.workshop_statuses'))
            ->where('starts_at', '>=', $from)
            ->where('starts_at', '<=', $to)
            ->with(['course.category', 'location'])
            ->get()
            ->map(fn (Workshop $workshop) => EphemerisEvent::make(
                id:          (string) $workshop->id,
                title:       $workshop->display_title,
                startsAt:    $workshop->starts_at,
                endsAt:      $workshop->ends_at,
                url:         route('magistere.workshops.show', $workshop),
                description: $workshop->display_summary,
                category:    $workshop->course->category?->name,
                colour:      $workshop->course->category?->colour,
            ));
    }
}
```

### Registration

```blade
<livewire:ephemeride-calendar 
    :provider="BlackpigCreatif\Magistere\Ephemeride\WorkshopProvider::class" 
/>
```

### Artisan command

`magistere:make-provider` — scaffolds a custom provider for apps needing filtering by category, trainer etc.

---

## 6. Atelier Blocks

Three blocks registered conditionally if Atelier is installed.

### 6.1 `magistere-calendar`

Embeds Ephemeride calendar showing Workshop events.

**Fields:** Provider class, views (month/week/both), default view, filterable toggle, theme overrides

### 6.2 `magistere-listing`

PLP-style grid of Workshop cards.

**Fields:** Heading (translatable), category filter (relation picker — Category model), status filter, limit, pagination toggle, card style (minimal/standard/featured)

**Card displays:** featured image, course title, workshop title override, date range, location, price, available spaces, CTA. Category filter renders as coloured pills.

### 6.3 `magistere-detail`

Workshop detail display. **CTA is state-driven** — no admin toggle needed:

```
draft/unpublished  → nothing (or coming soon stub)
unconfirmed        → "Register Interest" (EOI interest form)
published/confirmed → "Book Now" (booking form)
full               → "Join Waitlist" (EOI waitlist form)
cancelled          → Cancelled message
completed          → nothing (or archive message)
```

**Fields:** Workshop (relation picker), show itinerary toggle, show trainers toggle, show extras toggle, map embed toggle

---

## 7. Front-End Templates

Published via `php artisan vendor:publish --tag="magistere-views"`.

### 7.1 `magistere::listing`

Workshop grid, filter bar (category as coloured pills, date range, location), pagination.

### 7.2 `magistere::detail`

Workshop header, description, itinerary (collapsible), extras list, state-driven CTA/form, related workshops.

### 7.3 Livewire Components

| Component | Description |
|---|---|
| `magistere-booking-form` | Multi-step: attendee count → attendee details → extras → summary → submit. Tokenised pre-population skips to step 2. Token validated on mount — expired tokens show friendly message. |
| `magistere-eoi-form` | Single component, `type` prop: `interest` or `waitlist`. Fields identical, copy differs. |
| `magistere-workshop-card` | Reusable card for listing contexts |

---

## 8. Package Structure

```
blackpig-creatif/magistere/
  config/
    magistere.php
  database/
    migrations/
    factories/
    seeders/
  resources/
    views/
      livewire/
      listing.blade.php
      detail.blade.php
    lang/
      en/
        magistere.php
  src/
    Atelier/
      Blocks/
        MagistereCalendarBlock.php
        MagistereListingBlock.php
        MagistereDetailBlock.php
    Commands/
      MakeProviderCommand.php
    Contracts/
    Enums/
      WorkshopStatus.php
      BookingStatus.php
      PaymentStatus.php
      PaymentMethod.php
      EoiSource.php
      EoiStatus.php
      CourseLevel.php
      ExtraPer.php
    Ephemeride/
      WorkshopProvider.php
    Filament/
      Pages/
        ManageSettings.php
      Resources/
        CategoryResource.php
        CourseResource.php
        WorkshopResource.php
        BookingResource.php
        LocationResource.php
        TrainerResource.php
      Resources/Pages/
      RelationManagers/
      Widgets/
        UpcomingWorkshopsWidget.php
        BookingStatsWidget.php
        CalendarWidget.php
    Livewire/
      BookingForm.php
      EoiForm.php
      WorkshopCard.php
    Models/
      Category.php
      Course.php
      Workshop.php
      Location.php
      Trainer.php
      ItineraryItem.php
      Extra.php
      Booking.php
      Attendee.php
      Payment.php
      ExpressionOfInterest.php
      Setting.php
    MagisterePlugin.php
    MagistereServiceProvider.php
  tests/
    Feature/
    Unit/
  composer.json
  PLANNING.md
  README.md
```

---

## 9. Service Provider & Plugin

### MagistereServiceProvider

- Registers migrations, views, lang, config
- Registers Livewire components
- Registers artisan commands
- Conditionally registers Ephemeride provider if Ephemeride installed
- Conditionally registers Atelier blocks if Atelier installed

### MagisterePlugin (Filament Plugin)

```php
->plugins([
    MagisterePlugin::make()
        ->navigationGroup('Magistère')    // default, override per client
        ->withCalendarWidget()
        ->withStatsWidget()
        ->withSettingsPage()              // default: true
        ->withCategoryResource(),         // default: true
])
```

---

## 10. Config Reference (`config/magistere.php`)

```php
return [
    'currency'              => 'EUR',
    'per_page'              => 12,
    'route_prefix'          => 'magistere',
    'route_middleware'      => ['web'],

    'features' => [
        'expressions_of_interest' => true,
        'itinerary'               => true,
        'extras'                  => true,
        'trainers'                => true,
        'locations'               => true,
    ],

    'booking' => [
        'reference_prefix'          => 'MAG',           // fully configurable
        'require_gdpr_consent'      => true,
        'collect_marketing_consent' => true,
        'deposit_percentage'        => 25,
        'payment_methods'           => ['bank_transfer', 'cash', 'cheque', 'card_manual', 'other'],
        'token_expiry_hours'        => 72,               // EOI tokenised link expiry
    ],

    // Category colour palette — constrained for design integrity
    'category_colours'      => [
        '#e63946', '#2a9d8f', '#e9c46a', '#457b9d',
        '#a8dadc', '#f4a261', '#264653', '#e76f51',
    ],

    // Ephemeride integration
    // Note: add 'completed' to workshop_statuses to show historical workshops in calendar
    'ephemeride' => [
        'provider'           => \BlackpigCreatif\Magistere\Ephemeride\WorkshopProvider::class,
        'workshop_statuses'  => ['published', 'confirmed'],
    ],
];
```

---

## 11. Notifications (Stubs — v1)

Stubbed and registered but not fully implemented in v1:

- `WorkshopConfirmedNotification` — to confirmed bookings when workshop confirms
- `WorkshopCancelledNotification` — to confirmed bookings when workshop cancels
- `BookingConfirmedNotification` — to primary contact on confirmation
- `BookingWaitlistedNotification` — to primary contact if waitlisted
- `EoiReceivedNotification` — to admin on new EOI submission
- `EoiBookingInvitationNotification` — tokenised booking link (interest and waitlist paths)

---

## 12. Dependencies

```json
{
  "require": {
    "php": "^8.3",
    "laravel/framework": "^12.0",
    "filament/filament": "^5.0",
    "livewire/livewire": "^4.0"
  },
  "require-dev": {
    "orchestra/testbench": "^10.0",
    "pestphp/pest": "^3.0"
  },
  "suggest": {
    "blackpig-creatif/ephemeride": "Calendar front-end integration",
    "blackpig-creatif/atelier": "Content block system integration",
    "blackpig-creatif/chambre-noir": "Media management",
    "blackpig-creatif/sceau": "SEO meta management"
  }
}
```

Note: `spatie/laravel-settings` is intentionally NOT a dependency. Settings are managed via the `Setting` model and `ManageSettings` Filament page.

---

## 13. Build Order

```
1.  Package scaffold (composer.json, service provider, config)
2.  Enums (all — models depend on them)
3.  Migrations (dependency order):
      locations, trainers, categories,
      courses, workshops, trainer_workshop pivot,
      itinerary_items, extras, booking_extra + attendee_extra pivots,
      settings, bookings, attendees, payments,
      expressions_of_interest
4.  Models + relationships + factories
5.  Filament Resources — simpler first:
      Location, Trainer, Category,
      then Course, then Workshop, then Booking
6.  ManageSettings page + CategoryResource
7.  Relation Managers
8.  Filament Actions (confirm, cancel, duplicate, convert EOI,
      notify interest list, offer place, re-send notification,
      confirm booking, move to waitlist, check in)
9.  Ephemeride provider + magistere:make-provider artisan command
10. Livewire components (WorkshopCard, EoiForm, BookingForm
      with token pre-population and expiry handling)
11. Blade templates (listing, detail)
12. Atelier blocks (conditional registration)
13. Widgets (calendar, stats, upcoming workshops)
14. Tests
```

---

## 14. Open Questions / Decisions for Later

- **Completed workshops in calendar:** should `completed` status be included in `ephemeride.workshop_statuses` by default, or remain opt-in? Allows historical browsing of past workshops. Decision pending.
- **Translatable fields:** Spatie Translatable assumed — confirm consistent with rest of stack
- **ChambreNoir integration:** confirm media field API once ChambreNoir v1 stable
- **Sceau integration:** SEO meta field contract for Course and Workshop
- **Multi-panel support:** does Magistère need to support multiple Filament panels? Deferred.
- **Cascading waitlist automation:** auto-offer next on waitlist when booking cancelled. Deferred to v2.
- **`auto_notify_on_confirm` config flag:** automatically notify interest EOIs when workshop confirmed. Deferred to v2.
- **Recurring workshops:** RRULE-based recurrence. Deferred to v2.
- **Online/virtual event links:** location nullable covers for now. Deferred to v2.
- **Online payment processing:** full basket/checkout deferred to separate future package.


## Output
Produce a complete Filament Blueprint plan only. Do not write any implementation code yet. If anything in the requirements is ambiguous or would require a decision during implementation, flag it in the plan's clarifications section before proceeding.
```

---

## Usage Notes

- Replace `[PASTE REQUIREMENTS DOC CONTENTS HERE]` with the full contents of the relevant requirements markdown
- The prompt deliberately does not mention specific model names or table names — those come from the requirements doc
- After CC produces the plan, review it before approving implementation — especially migration definitions, the relation manager tab structure, and the traits list
- If CC skips the Blueprint and starts coding, repeat: *"Stop — enter plan mode and produce a Blueprint plan first"*
- If the plan doesn't include a traits analysis section, ask for one before approving
