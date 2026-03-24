<section class="relative overflow-hidden rounded-[36px] border border-emerald-100 bg-white/90 shadow-[0_30px_80px_rgba(15,118,110,0.14)]">
    <div class="absolute inset-x-0 top-0 h-32 bg-[linear-gradient(135deg,rgba(16,185,129,0.14),rgba(14,165,233,0.08),rgba(251,191,36,0.12))]"></div>
    <div class="absolute -top-10 right-8 h-40 w-40 rounded-full bg-emerald-200/40 blur-3xl"></div>
    <div class="absolute bottom-0 left-0 h-44 w-44 rounded-full bg-cyan-100/60 blur-3xl"></div>

    <div class="relative p-5 sm:p-7 lg:p-8">
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_340px]">
            <div class="space-y-6">
                @include('main_menu.partials.privileged.hero')
                @include('main_menu.partials.privileged.system-menu')
            </div>

            @include('main_menu.partials.privileged.sidebar')
        </div>
    </div>
</section>
