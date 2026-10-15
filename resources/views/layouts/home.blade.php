<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ env('APP_NAME') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body>
    <div>
        <nav class="py-4 border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4">
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('home') }}" class="text-2xl text-indigo-700 font-semibold">
                        Booking App
                    </a>
                    <a href="{{ route('home') }}"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700 transition">
                        Booking Now
                    </a>
                </div>
            </div>
        </nav>

        <main class="py-12">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>

    @livewireScripts
    @stack('scripts')
</body>

</html>
