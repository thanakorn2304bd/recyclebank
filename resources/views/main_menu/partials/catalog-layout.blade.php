<div class="{{ $authUser ? 'lg:flex lg:gap-8' : '' }}">
    @auth
        @include('main_menu.partials.member-sidebar')
    @endauth

    <main class="flex-1">
        @include('main_menu.partials.material-catalog')

        <div class="mt-4 text-xs text-emerald-700/70">
            หมายเหตุ: {{ $selectedPriceNotice }}.
        </div>
    </main>
</div>
