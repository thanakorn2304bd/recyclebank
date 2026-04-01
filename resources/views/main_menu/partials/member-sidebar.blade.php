<aside class="mb-6 lg:mb-0 lg:w-72 xl:w-80">
    <div class="space-y-4 lg:sticky lg:top-6">
        <div class="flex items-center gap-4 rounded-2xl border border-emerald-100 bg-white/90 p-4 shadow-sm">
            <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-50">
                <img src="{{ asset('images/recycle-logo.png') }}" alt="โลโก้กองทุนธนาคารวัสดุรีไซเคิ้ล" class="h-14 w-14 object-contain">
            </div>
            <div>
                <div class="text-lg font-bold leading-tight text-emerald-900">กองทุนธนาคารวัสดุรีไซเคิ้ล</div>
                <div class="text-sm text-emerald-700/80">เทศบาลตำบลหนองไผ่</div>
            </div>
        </div>

        <nav class="space-y-2">
            <a href="{{ route('transactions.index') }}" class="group flex items-center gap-3 rounded-xl border border-emerald-100/60 bg-white px-3 py-2.5 text-sm font-semibold text-emerald-900 shadow-sm transition hover:border-emerald-200 hover:shadow-md">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-blue-100 text-blue-700 group-hover:bg-blue-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </span>
                ประวัติรายการ
            </a>

            <a href="{{ route('withdraw-requests.index') }}" class="group flex items-center gap-3 rounded-xl border border-emerald-100/60 bg-white px-3 py-2.5 text-sm font-semibold text-emerald-900 shadow-sm transition hover:border-emerald-200 hover:shadow-md">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-100 text-amber-700 group-hover:bg-amber-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V5a4 4 0 118 0v2m-9 0h10a2 2 0 012 2v9a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2zm5 4v4m0 0l-2-2m2 2l2-2"/>
                    </svg>
                </span>
                คำขอถอน
            </a>

            <a href="{{ route('reports.index') }}" class="group flex items-center gap-3 rounded-xl border border-emerald-100/60 bg-white px-3 py-2.5 text-sm font-semibold text-emerald-900 shadow-sm transition hover:border-emerald-200 hover:shadow-md">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-cyan-100 text-cyan-700 group-hover:bg-cyan-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 19h16M7 16V9m5 7V5m5 11v-4"/>
                    </svg>
                </span>
                สรุปรายงาน
            </a>

            <a href="{{ route('households.index') }}" class="group flex items-center gap-3 rounded-xl border border-emerald-100/60 bg-white px-3 py-2.5 text-sm font-semibold text-emerald-900 shadow-sm transition hover:border-emerald-200 hover:shadow-md">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-100 text-indigo-700 group-hover:bg-indigo-200">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10.5L12 4l9 6.5v8a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-8z"/>
                    </svg>
                </span>
                ครัวเรือน
            </a>
        </nav>

        <div class="space-y-3 rounded-xl border border-emerald-100 bg-white/80 px-4 py-3 text-xs text-emerald-700">
            <div class="flex items-center gap-2">
                <svg class="h-4 w-4 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                </svg>
                <span>อัปเดตล่าสุด {{ $updatedAtLabel }} น.</span>
            </div>
            <div class="text-emerald-800">ผู้ใช้: {{ $authUser->username }} ({{ $authUser->role }})</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-100">
                    ออกจากระบบ
                </button>
            </form>
        </div>
    </div>
</aside>
