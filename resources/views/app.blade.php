<!DOCTYPE html>
{{-- lang + dir follow the active locale (RFC-005 / D-027): Core is
     locale-neutral, English by default, and honours APP_LOCALE. --}}
@php($locale = app()->getLocale())
<html lang="{{ $locale }}" dir="{{ \App\Core\Support\Locale::direction($locale) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title inertia>{{ config('penova.name') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any" />
        <link rel="icon" type="image/png" href="/penova-logo.png" />
        <link rel="apple-touch-icon" href="/penova-logo.png" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="bg-slate-100 font-sans antialiased">
        @inertia
    </body>
</html>
