<nav x-data="{ open: false }" class="border-b border-emerald-100 bg-white/85 backdrop-blur">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-18 items-center justify-between gap-4 py-3">
            <div class="flex items-center gap-6">
                <a href="{{ route('main-menu') }}" class="flex items-center gap-3 text-emerald-900">
                    <img src="{{ asset('images/recycle-logo.png') }}" alt="โลโก้ธนาคารวัสดุรีไซเคิล" class="h-10 w-10 rounded-2xl bg-white p-1.5 shadow-sm ring-1 ring-emerald-100 object-contain">
                    <div>
                        <div class="text-sm font-semibold tracking-tight sm:text-base">ธนาคารวัสดุรีไซเคิล</div>
                        <div class="text-xs text-emerald-700/70">ระบบจัดการข้อมูลและบริการครัวเรือน</div>
                    </div>
                </a>

                <div class="hidden items-center gap-2 md:flex">
                    <a href="{{ route('main-menu') }}" class="inline-flex items-center rounded-full px-3 py-2 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-50">
                        หน้าหลัก
                    </a>
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center rounded-full px-3 py-2 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-50">
                        Dashboard
                    </a>
                    <a href="{{ route('reports.index') }}" class="inline-flex items-center rounded-full px-3 py-2 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-50">
                        รายงาน
                    </a>
                </div>
            </div>

            @auth
                <div class="hidden sm:flex sm:items-center sm:gap-3">
                    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 px-3 py-2 text-right">
                        <div class="text-sm font-semibold text-emerald-900">{{ Auth::user()->username }}</div>
                        <div class="text-xs text-emerald-700/70">สิทธิ์ {{ Auth::user()->role }}</div>
                    </div>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 rounded-2xl border border-emerald-100 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-800 shadow-sm transition hover:border-emerald-200 hover:bg-emerald-50 focus:outline-none">
                                จัดการบัญชี
                                <svg class="h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('account.password.edit')">
                                เปลี่ยนรหัสผ่าน
                            </x-dropdown-link>

                            <x-dropdown-link :href="route('reports.index')">
                                สรุปรายงาน
                            </x-dropdown-link>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                    ออกจากระบบ
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>
            @endauth

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-2xl border border-emerald-100 bg-white p-2 text-emerald-700 shadow-sm transition hover:bg-emerald-50 focus:outline-none">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden border-t border-emerald-100 bg-white sm:hidden">
        <div class="space-y-1 px-4 pb-3 pt-3">
            <x-responsive-nav-link :href="route('main-menu')">
                หน้าหลัก
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('dashboard')">
                Dashboard
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('reports.index')">
                สรุปรายงาน
            </x-responsive-nav-link>
        </div>

        @auth
            <div class="border-t border-emerald-100 px-4 py-4">
                <div class="font-medium text-base text-emerald-900">{{ Auth::user()->username }}</div>
                <div class="font-medium text-sm text-emerald-700/70">{{ Auth::user()->role }}</div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('account.password.edit')">
                        เปลี่ยนรหัสผ่าน
                    </x-responsive-nav-link>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                            ออกจากระบบ
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @endauth
    </div>
</nav>
