@extends('magistere::layouts.app')

@php
    $locale      = app()->getLocale();
    $title       = $workshop->display_title;
    $displayTitle = is_array($title)
        ? ($title[$locale] ?? $title['en'] ?? array_values($title)[0] ?? $workshop->slug)
        : (string) ($title ?? $workshop->slug);
@endphp

@section('title', __('magistere::eoi.register_heading') . ' — ' . $displayTitle)

@section('content')
    <div class="magistere-interest-page">
        <header class="magistere-interest-page__header">
            <h1>{{ __('magistere::eoi.register_heading') }}</h1>
            <p>{{ $displayTitle }} — {{ $workshop->starts_at->format('d M Y') }}</p>
        </header>

        <livewire:magistere.eoi-form
            :workshop="$workshop"
            :key="'eoi-'.$workshop->id"
        />
    </div>
@endsection
