<x-app-layout>
    @php
        $user = auth()->user();
        $isPrivileged = $user && in_array($user->role, ['admin', 'staff'], true);
    @endphp

    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    Dashboard
                </div>
                <h2 class="mt-3 text-2xl font-bold tracking-tight text-emerald-950">
                    ศูนย์ควบคุมการใช้งาน
                </h2>
                <p class="mt-1 text-sm text-emerald-800/75">
                    ตรวจสอบภาพรวมและเข้าใช้งานเมนูหลักได้อย่างรวดเร็วจากจุดเดียว
                </p>
            </div>
            <a href="{{ route('main-menu') }}" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                ไปหน้าเมนูหลัก
            </a>
        </div>
    </x-slot>

    <div class="bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.12),_transparent_35%),linear-gradient(180deg,#f3fbf7_0%,#eef7f2_46%,#f8fcfa_100%)] py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-3xl border border-emerald-100 bg-white/90 p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">ผู้ใช้งาน</div>
                    <div class="mt-3 text-2xl font-bold text-emerald-950">{{ $user?->username }}</div>
                    <div class="mt-2 text-sm text-emerald-800/75">สิทธิ์ {{ $user?->role }}</div>
                </div>
                <div class="rounded-3xl border border-emerald-100 bg-white/90 p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">สถานะบัญชี</div>
                    <div class="mt-3 text-2xl font-bold text-emerald-950">พร้อมใช้งาน</div>
                    <div class="mt-2 text-sm text-emerald-800/75">เข้าใช้งานเมนูต่าง ๆ ได้ตามสิทธิ์ที่ได้รับ</div>
                </div>
                <div class="rounded-3xl border border-emerald-100 bg-white/90 p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">เมนูหลัก</div>
                    <div class="mt-3 text-2xl font-bold text-emerald-950">{{ $isPrivileged ? 'จัดการระบบ' : 'ดูข้อมูลส่วนตัว' }}</div>
                    <div class="mt-2 text-sm text-emerald-800/75">
                        {{ $isPrivileged ? 'เข้าถึงธุรกรรม รายงาน และข้อมูลหลักของระบบ' : 'ดูข้อมูลครัวเรือนและประวัติรายการของตนเอง' }}
                    </div>
                </div>
                <div class="rounded-3xl border border-emerald-100 bg-white/90 p-5 shadow-sm">
                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">ทางลัด</div>
                    <div class="mt-3 text-2xl font-bold text-emerald-950">พร้อมเริ่มงาน</div>
                    <div class="mt-2 text-sm text-emerald-800/75">เลือกเมนูด้านล่างเพื่อทำงานต่อได้ทันที</div>
                </div>
            </div>

            <div class="mt-6 rounded-[30px] border border-emerald-100 bg-white/90 p-6 shadow-sm">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <div class="text-sm font-semibold text-emerald-700">เมนูแนะนำ</div>
                        <h3 class="mt-1 text-2xl font-bold text-emerald-950">ทางลัดสำหรับการใช้งานประจำวัน</h3>
                    </div>
                    <p class="text-sm text-emerald-800/70">เลือกเมนูที่ใช้บ่อยเพื่อไปทำงานต่อได้ทันที</p>
                </div>

                <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                    <a href="{{ route('main-menu') }}" class="group rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50">
                        <div class="text-sm font-semibold text-emerald-800">เมนูหลัก</div>
                        <div class="mt-2 text-lg font-bold text-emerald-950">ดูราคาวัสดุและเมนูทั้งหมด</div>
                        <div class="mt-2 text-sm text-emerald-800/75">ศูนย์รวมเมนูสำหรับใช้งานประจำวัน</div>
                    </a>

                    <a href="{{ route('reports.index') }}" class="group rounded-2xl border border-emerald-100 bg-white p-4 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50/70">
                        <div class="text-sm font-semibold text-emerald-800">รายงาน</div>
                        <div class="mt-2 text-lg font-bold text-emerald-950">สรุปรายงานประจำวัน</div>
                        <div class="mt-2 text-sm text-emerald-800/75">ดูภาพรวมการรับซื้อและการถอน</div>
                    </a>

                    <a href="{{ route('transactions.index') }}" class="group rounded-2xl border border-emerald-100 bg-white p-4 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50/70">
                        <div class="text-sm font-semibold text-emerald-800">ธุรกรรม</div>
                        <div class="mt-2 text-lg font-bold text-emerald-950">ประวัติรายการ</div>
                        <div class="mt-2 text-sm text-emerald-800/75">ค้นหาและเปิดดูรายละเอียดรายการย้อนหลัง</div>
                    </a>

                    <a href="{{ route('households.index') }}" class="group rounded-2xl border border-emerald-100 bg-white p-4 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50/70">
                        <div class="text-sm font-semibold text-emerald-800">ครัวเรือน</div>
                        <div class="mt-2 text-lg font-bold text-emerald-950">จัดการสมาชิก</div>
                        <div class="mt-2 text-sm text-emerald-800/75">ตรวจสอบบัญชีและข้อมูลผู้ติดต่อ</div>
                    </a>

                    @if($isPrivileged)
                        <a href="{{ route('deposits.create') }}" class="group rounded-2xl border border-emerald-100 bg-white p-4 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50/70">
                            <div class="text-sm font-semibold text-emerald-800">รับซื้อ</div>
                            <div class="mt-2 text-lg font-bold text-emerald-950">ฝาก/รับซื้อวัสดุ</div>
                            <div class="mt-2 text-sm text-emerald-800/75">ทำรายการรับซื้อและบันทึกยอดเข้า</div>
                        </a>

                        <a href="{{ route('withdraws.create') }}" class="group rounded-2xl border border-emerald-100 bg-white p-4 transition hover:-translate-y-0.5 hover:border-emerald-200 hover:bg-emerald-50/70">
                            <div class="text-sm font-semibold text-emerald-800">ถอน</div>
                            <div class="mt-2 text-lg font-bold text-emerald-950">ถอนเงินและพิมพ์ใบถอน</div>
                            <div class="mt-2 text-sm text-emerald-800/75">ตรวจสอบยอดคงเหลือก่อนถอนเงินจริง</div>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
