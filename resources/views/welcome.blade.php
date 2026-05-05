<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PokeApp CV</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[radial-gradient(circle_at_top,_#ffedd5_0%,_#fffbeb_40%,_#f8fafc_100%)] px-4 py-10 text-slate-900 sm:px-8">
        <div id="app" class="mx-auto w-full max-w-5xl"></div>
    </body>
</html>
