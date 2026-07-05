<!DOCTYPE html>
{{-- Persian-only for now; when more locales land, derive dir/lang from
     the active locale instead of hard-coding. --}}
<html lang="fa" dir="rtl">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <title inertia>{{ config('penova.name') }}</title>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @inertiaHead
    </head>
    <body class="bg-slate-100 font-sans antialiased">
        @inertia
    </body>
</html>
