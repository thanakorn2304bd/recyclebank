<x-app-layout>
    <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.14),_transparent_38%),linear-gradient(135deg,#f3fbf7_0%,#eefaf5_45%,#f8fffc_100%)]">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8">
            @guest
                @include('main_menu.partials.guest-hero')
            @endguest

            @if($isPrivileged)
                @include('main_menu.partials.privileged-dashboard')
            @else
                @include('main_menu.partials.catalog-layout')
            @endif
        </div>
    </div>
</x-app-layout>
