<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="light" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="color-scheme" content="light">
    <title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen font-sans antialiased bg-base-200">

    {{-- NAVBAR mobile only --}}
    <x-nav sticky class="lg:hidden">
        <x-slot:brand>
            <x-app-brand />
        </x-slot:brand>
        <x-slot:actions>
            <label for="main-drawer" class="lg:hidden me-3">
                <x-icon name="o-bars-3" class="cursor-pointer" />
            </label>
        </x-slot:actions>
    </x-nav>

    {{-- MAIN --}}
    <x-main>
        {{-- SIDEBAR --}}
        <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

            {{-- BRAND --}}
            <x-app-brand class="px-5 pt-4" />

            {{-- MENU --}}
            <x-menu activate-by-route>

                @if($user = auth()->user())
                    <x-menu-separator />

                    <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover class="-mx-2 !-my-2 rounded">
                        <x-slot:actions>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-button type="submit" icon="o-power" class="btn-circle btn-ghost btn-xs" tooltip-left="logout" />
                            </form>
                        </x-slot:actions>
                    </x-list-item>

                    <x-menu-separator />
                @endif

                <x-menu-item title="Pendaftar" icon="o-clipboard-document-list" link="{{ route('admin.registrations') }}" />
                <x-menu-item title="Manajemen Tim" icon="o-user-group" link="{{ route('admin.teams') }}" />
                <x-menu-item title="Setting" icon="o-cog-6-tooth" link="{{ route('admin.settings') }}" />
                <x-menu-item title="Landing page" icon="o-globe-alt" link="{{ route('landing') }}" />
            </x-menu>
        </x-slot:sidebar>

        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    <x-toast />
</body>
</html>
