<x-app-layout>
    @php
        $authUser = auth()->user();
        $isPrivileged = $authUser && in_array($authUser->role, ['admin', 'staff'], true);
    @endphp
    <div class="bg-gradient-to-br from-emerald-50 via-green-50 to-teal-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            <div class="lg:flex lg:gap-8">
                <!-- Sidebar -->
                <aside class="lg:w-72 xl:w-80 mb-6 lg:mb-0">
                    <div class="lg:sticky lg:top-6 space-y-4">
                        <div class="flex items-center gap-4 rounded-2xl bg-white/90 p-4 shadow-sm border border-emerald-100">
                            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50">
                                <img src="{{ asset('images/recycle-logo.png') }}" alt="โลโก้กองทุนธนาคารวัสดุรีไซเคิ้ล" class="h-14 w-14 object-contain">
                            </div>
                            <div>
                                <div class="text-lg font-bold text-emerald-900 leading-tight">กองทุนธนาคารวัสดุรีไซเคิ้ล</div>
                                <div class="text-sm text-emerald-700/80">เทศบาลตำบลหนองไผ่</div>
                            </div>
                        </div>

                        <nav class="space-y-2">
                            @if(!$authUser || $isPrivileged)
                                <a href="{{ route('deposits.create') }}" class="group flex items-center gap-4 rounded-2xl bg-emerald-600 px-4 py-4 text-base font-bold text-white shadow-md border border-emerald-500 hover:bg-emerald-700 hover:shadow-lg transition">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 text-white">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </span>
                                    รับซื้อวัสดุรีไซเคิ้ล
                                </a>

                                <a href="{{ route('withdraws.create') }}" class="group flex items-center gap-4 rounded-2xl bg-amber-500 px-4 py-4 text-base font-bold text-white shadow-md border border-amber-400 hover:bg-amber-600 hover:shadow-lg transition">
                                    <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 text-white">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                                        </svg>
                                    </span>
                                    ถอน
                                </a>
                            @endif

                            <a href="{{ route('transactions.index') }}" class="group flex items-center gap-3 rounded-xl bg-white px-3 py-2.5 text-sm font-semibold text-emerald-900 shadow-sm border border-emerald-100/60 hover:border-emerald-200 hover:shadow-md transition">
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-blue-700 group-hover:bg-blue-200">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </span>
                                ประวัติรายการ
                            </a>

                            @if(!$authUser || $isPrivileged)
                                <a href="{{ route('materials.index') }}" class="group flex items-center gap-3 rounded-xl bg-white px-3 py-2.5 text-sm font-semibold text-emerald-900 shadow-sm border border-emerald-100/60 hover:border-emerald-200 hover:shadow-md transition">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-100 text-teal-700 group-hover:bg-teal-200">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </span>
                                    รายการวัสดุ
                                </a>
                            @endif

                            <a href="{{ route('households.index') }}" class="group flex items-center gap-3 rounded-xl bg-white px-3 py-2.5 text-sm font-semibold text-emerald-900 shadow-sm border border-emerald-100/60 hover:border-emerald-200 hover:shadow-md transition">
                                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 group-hover:bg-indigo-200">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10.5L12 4l9 6.5v8a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-8z"/>
                                    </svg>
                                </span>
                                ครัวเรือน
                            </a>

                            @if(!$authUser || $isPrivileged)
                                <a href="{{ route('material-categories.index') }}" class="group flex items-center gap-3 rounded-xl bg-white px-3 py-2.5 text-sm font-semibold text-emerald-900 shadow-sm border border-emerald-100/60 hover:border-emerald-200 hover:shadow-md transition">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-rose-100 text-rose-700 group-hover:bg-rose-200">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                        </svg>
                                    </span>
                                    หมวดหมู่วัสดุ
                                </a>

                                <a href="{{ route('material-prices.index') }}" class="group flex items-center gap-3 rounded-xl bg-white px-3 py-2.5 text-sm font-semibold text-emerald-900 shadow-sm border border-emerald-100/60 hover:border-emerald-200 hover:shadow-md transition">
                                    <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-lime-100 text-lime-700 group-hover:bg-lime-200">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </span>
                                    ราคาวัสดุ
                                </a>
                            @endif
                        </nav>

                        <div class="rounded-xl bg-white/80 px-4 py-3 text-xs text-emerald-700 border border-emerald-100 space-y-3">
                            <div class="flex items-center gap-2">
                                <svg class="h-4 w-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                <span>อัปเดตล่าสุด {{ now()->format('d/m/Y H:i') }} น.</span>
                            </div>
                            @guest
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-700 transition">
                                    เข้าสู่ระบบเพื่อใช้งานเมนูอื่น
                                </a>
                            @else
                                <div class="text-emerald-800">
                                    ผู้ใช้: {{ $authUser->username }} ({{ $authUser->role }})
                                </div>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-emerald-300 bg-white px-3 py-2 text-xs font-semibold text-emerald-700 hover:bg-emerald-50 transition">
                                        ออกจากระบบ
                                    </button>
                                </form>
                            @endguest
                        </div>
                    </div>
                </aside>

                <!-- Main Content -->
                <main class="flex-1">
                    <div class="rounded-3xl bg-white/90 shadow-sm border border-emerald-100">
                        <div class="flex flex-col gap-3 border-b border-emerald-100 p-4 sm:p-6">
                            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <h2 class="text-xl sm:text-2xl font-bold text-emerald-900">รายการวัสดุและราคา</h2>
                                    <p class="text-sm text-emerald-700/70">แสดงราคาปัจจุบันที่มีผลใช้งาน</p>
                                </div>
                                @if(!$authUser || $isPrivileged)
                                    <div class="flex flex-wrap items-center gap-2">
                                        <a href="{{ route('material-prices.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 transition">
                                            จัดการราคา
                                        </a>
                                        <a href="{{ route('materials.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-white px-3 py-2 text-sm font-semibold text-emerald-700 border border-emerald-200 hover:border-emerald-300 hover:text-emerald-800 transition">
                                            จัดการวัสดุ
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="p-4 sm:p-6">
                            @php
                                $grouped = $materials
                                    ->sortBy(function ($m) {
                                        return ($m->category?->category_name ?? 'ไม่ระบุหมวด') . '|' . $m->material_name;
                                    })
                                    ->groupBy(function ($m) {
                                        return $m->category?->category_name ?? 'ไม่ระบุหมวด';
                                    });
                            @endphp

                            @forelse($grouped as $categoryName => $items)
                                <div class="mb-6 last:mb-0">
                                    <div class="flex items-center gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                        <h3 class="text-lg font-semibold text-emerald-900">{{ $categoryName }}</h3>
                                        <span class="rounded-full border border-emerald-100 bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">
                                            {{ $items->count() }} รายการ
                                        </span>
                                    </div>

                                    <div class="mt-3 rounded-2xl border border-emerald-200 bg-emerald-100 p-px shadow-sm overflow-hidden">
                                        <div class="grid gap-px bg-emerald-100 sm:grid-cols-2 lg:grid-cols-3">
                                            @foreach($items as $m)
                                                @php
                                                    $currentPrice = $m->prices->first();
                                                @endphp
                                                <div class="bg-white p-4">
                                                    <div class="text-base font-semibold text-emerald-900">{{ $m->material_name }}</div>
                                                    <div class="mt-1 text-sm text-emerald-700">
                                                        ราคา/หน่วย:
                                                        @if($currentPrice)
                                                            <span class="font-semibold text-emerald-900">{{ number_format((float) $currentPrice->price, 2) }}</span>
                                                            <span class="text-emerald-700">บาท/{{ $m->unit }}</span>
                                                        @else
                                                            <span class="text-amber-600">ยังไม่มีราคา</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="rounded-2xl border border-emerald-100 bg-white p-6 text-center text-emerald-700/70">
                                    ยังไม่มีรายการวัสดุ
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="mt-4 text-xs text-emerald-700/70">
                        หมายเหตุ: ราคาเป็นราคาปัจจุบันที่มีผล ณ {{ now()->format('d/m/Y') }}.
                    </div>
                </main>
            </div>
        </div>
    </div>
</x-app-layout>
