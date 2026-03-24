<x-app-layout>
    @php
        $authUser = auth()->user();
        $isPrivileged = $authUser && in_array($authUser->role, ['admin', 'staff'], true);
        $roleLabel = match ($authUser?->role) {
            'admin' => 'ผู้ดูแลระบบ',
            'staff' => 'เจ้าหน้าที่',
            default => 'สมาชิก',
        };
        $privilegedMenuCount = $authUser?->role === 'admin' ? 8 : 6;
        $grouped = $materials
            ->sortBy(function ($m) {
                return ($m->category?->category_name ?? 'ไม่ระบุหมวด') . '|' . $m->material_name;
            })
            ->groupBy(function ($m) {
                return $m->category?->category_name ?? 'ไม่ระบุหมวด';
            });
        $categoryFilters = $grouped->map(function ($items, $categoryName) {
            return [
                'name' => $categoryName,
                'count' => $items->count(),
            ];
        })->values();
        $totalCategories = $grouped->count();
        $totalMaterials = $materials->count();
    @endphp

    <div class="min-h-screen bg-[radial-gradient(circle_at_top_left,_rgba(16,185,129,0.14),_transparent_38%),linear-gradient(135deg,#f3fbf7_0%,#eefaf5_45%,#f8fffc_100%)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8">
            @guest
                <section class="mb-6 overflow-hidden rounded-[30px] border border-emerald-100 bg-white/90 shadow-[0_24px_70px_rgba(15,118,110,0.12)]">
                    <div class="grid gap-8 px-6 py-7 lg:grid-cols-[1.15fr,0.85fr] lg:px-8">
                        <div>
                            <div class="inline-flex items-center gap-2 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                ธนาคารวัสดุรีไซเคิลเทศบาลตำบลหนองไผ่
                            </div>
                            <div class="mt-4 flex items-center gap-4">
                                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-emerald-50 shadow-inner shadow-emerald-100">
                                    <img src="{{ asset('images/recycle-logo.png') }}" alt="โลโก้กองทุนธนาคารวัสดุรีไซเคิ้ล" class="h-16 w-16 object-contain">
                                </div>
                                <div>
                                    <h1 class="text-3xl font-bold tracking-tight text-emerald-950 sm:text-4xl">ดูราคารับซื้อวันนี้ พร้อมสมัครสมาชิกครัวเรือนออนไลน์</h1>
                                    <p class="mt-2 max-w-2xl text-sm leading-6 text-emerald-800/80 sm:text-base">
                                        สมัครได้ด้วยตัวเองจากหน้าเว็บ ระบบจะบันทึกครัวเรือนไว้ในสถานะรออนุมัติ แล้ว staff/admin จะยืนยันก่อนเปิดให้เข้าสู่ระบบดูข้อมูลของครัวเรือนตัวเอง
                                    </p>
                                </div>
                            </div>

                            <div class="mt-6 flex flex-wrap gap-3">
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700">
                                    สมัครสมาชิก
                                </a>
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-semibold text-emerald-800 transition hover:border-emerald-300 hover:bg-emerald-50">
                                    ลงชื่อเข้าใช้
                                </a>
                            </div>

                            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                                <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 px-4 py-4">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">Step 1</div>
                                    <div class="mt-2 text-sm font-semibold text-emerald-950">กรอกข้อมูลครัวเรือน</div>
                                    <div class="mt-1 text-sm text-emerald-800/75">ระบุชุมชน บ้านเลขที่ ผู้ติดต่อ และตั้งรหัสผ่าน</div>
                                </div>
                                <div class="rounded-2xl border border-amber-100 bg-amber-50/90 px-4 py-4">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-amber-600">Step 2</div>
                                    <div class="mt-2 text-sm font-semibold text-amber-950">รอเจ้าหน้าที่ยืนยัน</div>
                                    <div class="mt-1 text-sm text-amber-900/75">บัญชีจะอยู่ในสถานะรออนุมัติจนกว่า staff/admin จะตรวจสอบ</div>
                                </div>
                                <div class="rounded-2xl border border-teal-100 bg-teal-50/90 px-4 py-4">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-teal-600">Step 3</div>
                                    <div class="mt-2 text-sm font-semibold text-teal-950">เข้าใช้ดูข้อมูลตนเอง</div>
                                    <div class="mt-1 text-sm text-teal-900/75">หลังอนุมัติแล้ว ใช้เลขบัญชีและรหัสผ่านที่สมัครไว้เข้าสู่ระบบ</div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[28px] bg-[linear-gradient(160deg,#0f766e_0%,#047857_55%,#065f46_100%)] p-6 text-white shadow-[0_20px_60px_rgba(6,95,70,0.24)]">
                            <div class="text-sm font-semibold tracking-[0.18em] text-emerald-100/90">บริการออนไลน์สำหรับครัวเรือน</div>
                            <h2 class="mt-3 text-2xl font-bold leading-tight">ไม่ต้องเริ่มจากหน้าเจ้าหน้าที่อีกต่อไป</h2>
                            <p class="mt-3 text-sm leading-6 text-emerald-50/90">
                                หน้านี้เปิดให้ดูข้อมูลราคาวัสดุได้ทันที และถ้าต้องการสมัครเข้าระบบก็สามารถส่งคำขอได้เองจากปุ่มด้านล่าง
                            </p>

                            <div class="mt-6 space-y-3">
                                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                                    <div class="text-sm font-semibold">ดูราคาปัจจุบันได้ทันที</div>
                                    <div class="mt-1 text-sm text-emerald-50/80">ไม่ต้องล็อกอินก็เช็กราคารับซื้อวัสดุแต่ละหมวดได้</div>
                                </div>
                                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                                    <div class="text-sm font-semibold">สมัครครัวเรือนด้วยเลขบัญชีอัตโนมัติ</div>
                                    <div class="mt-1 text-sm text-emerald-50/80">ระบบช่วยสร้างเลขบัญชีจากปีปัจจุบัน ชุมชน และบ้านเลขที่ให้ก่อน</div>
                                </div>
                                <div class="rounded-2xl border border-white/15 bg-white/10 px-4 py-4 backdrop-blur-sm">
                                    <div class="text-sm font-semibold">อนุมัติแล้วค่อยเข้าใช้ได้</div>
                                    <div class="mt-1 text-sm text-emerald-50/80">เจ้าหน้าที่สามารถเปิดใช้งานบัญชีภายหลังเพื่อให้ข้อมูลถูกต้องก่อนใช้งานจริง</div>
                                </div>
                            </div>

                            <div class="mt-6 rounded-2xl bg-white/12 px-4 py-4 text-sm text-emerald-50/90">
                                อัปเดตราคา ณ {{ now()->format('d/m/Y') }} และเปิดให้สมัครสมาชิกใหม่ได้ตลอดเวลา
                            </div>
                        </div>
                    </div>
                </section>
            @endguest

            @if($isPrivileged)
                <section class="relative overflow-hidden rounded-[36px] border border-emerald-100 bg-white/90 shadow-[0_30px_80px_rgba(15,118,110,0.14)]">
                    <div class="absolute inset-x-0 top-0 h-32 bg-[linear-gradient(135deg,rgba(16,185,129,0.14),rgba(14,165,233,0.08),rgba(251,191,36,0.12))]"></div>
                    <div class="absolute -top-10 right-8 h-40 w-40 rounded-full bg-emerald-200/40 blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 h-44 w-44 rounded-full bg-cyan-100/60 blur-3xl"></div>

                    <div class="relative p-5 sm:p-7 lg:p-8">
                        <div class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_340px]">
                            <div class="space-y-6">
                                <div class="relative overflow-hidden rounded-[32px] bg-[linear-gradient(140deg,#064e3b_0%,#0f766e_48%,#10b981_100%)] p-6 text-white shadow-[0_24px_70px_rgba(6,95,70,0.28)] sm:p-7">
                                    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="max-w-3xl">
                                            <div class="inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/10 px-3 py-1 text-xs font-semibold tracking-[0.16em] text-emerald-50/90">
                                                ADMIN / STAFF MENU
                                            </div>
                                            <h1 class="mt-4 text-3xl font-bold tracking-tight text-white sm:text-4xl">
                                                เมนูหลักสำหรับการทำงานประจำวัน
                                            </h1>
                                            <div class="mt-4 max-w-2xl">
                                                <div class="inline-flex items-center rounded-full border border-white/20 bg-white/12 px-3 py-1 text-[11px] font-semibold tracking-[0.18em] text-emerald-50/90">
                                                    RECYCLE BANK
                                                </div>
                                                <div class="mt-3 text-2xl font-bold leading-tight text-white sm:text-3xl">กองทุนธนาคารวัสดุรีไซเคิ้ล</div>
                                                <div class="mt-2 text-base font-semibold text-emerald-50/90 sm:text-lg">เทศบาลตำบลหนองไผ่</div>
                                                <div class="mt-3 text-sm leading-6 text-emerald-50/80 sm:text-base">
                                                    จุดศูนย์กลางสำหรับเมนูเจ้าหน้าที่และงานประจำวัน
                                                </div>
                                            </div>

                                            <div class="mt-6 flex flex-wrap gap-2 text-sm">
                                                <span class="inline-flex items-center rounded-full bg-white/12 px-3 py-2 font-medium text-emerald-50">
                                                    ผู้ใช้: {{ $authUser->username }}
                                                </span>
                                                <span class="inline-flex items-center rounded-full bg-white/12 px-3 py-2 font-medium text-emerald-50">
                                                    สิทธิ์: {{ $roleLabel }}
                                                </span>
                                                <span class="inline-flex items-center rounded-full bg-white/12 px-3 py-2 font-medium text-emerald-50">
                                                    อัปเดต {{ now()->format('d/m/Y H:i') }} น.
                                                </span>
                                            </div>
                                        </div>

                                        <div class="relative isolate overflow-hidden rounded-[30px] border border-white/20 bg-[linear-gradient(135deg,rgba(255,255,255,0.22),rgba(255,255,255,0.08))] p-5 shadow-[0_22px_50px_rgba(6,95,70,0.24)] backdrop-blur-md">
                                            <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full bg-amber-300/25 blur-2xl"></div>
                                            <div class="absolute -bottom-5 left-3 h-24 w-24 rounded-full bg-emerald-200/20 blur-2xl"></div>

                                            <div class="relative flex min-h-[260px] items-center justify-center sm:min-h-[300px]">
                                                <div class="flex h-36 w-36 shrink-0 items-center justify-center rounded-[34px] border border-white/30 bg-white/12 shadow-[0_18px_35px_rgba(4,120,87,0.24)] sm:h-44 sm:w-44">
                                                    <img src="{{ asset('images/recycle-logo.png') }}" alt="โลโก้กองทุนธนาคารวัสดุรีไซเคิ้ล" class="h-28 w-28 object-contain drop-shadow-[0_8px_14px_rgba(16,185,129,0.22)] sm:h-32 sm:w-32">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid gap-4 md:grid-cols-2">
                                    <a href="{{ route('deposits.create') }}" class="group relative overflow-hidden rounded-[28px] bg-[linear-gradient(140deg,#059669_0%,#10b981_70%,#6ee7b7_125%)] p-5 text-white shadow-[0_18px_50px_rgba(16,185,129,0.22)] transition duration-200 hover:-translate-y-1 hover:shadow-[0_24px_60px_rgba(16,185,129,0.28)]">
                                        <div class="absolute -right-10 top-0 h-28 w-28 rounded-full bg-white/10 blur-2xl"></div>
                                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 text-white shadow-inner shadow-emerald-900/10">
                                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M12 4v16m8-8H4"/>
                                            </svg>
                                        </span>
                                        <div class="relative mt-6">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="text-xl font-bold">รับซื้อวัสดุรีไซเคิ้ล</div>
                                                    <p class="mt-2 text-sm leading-6 text-emerald-50/90">
                                                        เริ่มบันทึกรายการรับซื้อ ชั่งน้ำหนัก และคำนวณยอดฝากของครัวเรือนได้ทันที
                                                    </p>
                                                </div>
                                                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">เมนูหลัก</span>
                                            </div>
                                            <div class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-white">
                                                เปิดหน้ารับซื้อ
                                                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </a>

                                    <a href="{{ route('withdraws.create') }}" class="group relative overflow-hidden rounded-[28px] bg-[linear-gradient(140deg,#f59e0b_0%,#f97316_68%,#fdba74_130%)] p-5 text-white shadow-[0_18px_50px_rgba(249,115,22,0.22)] transition duration-200 hover:-translate-y-1 hover:shadow-[0_24px_60px_rgba(249,115,22,0.26)]">
                                        <div class="absolute -right-10 top-0 h-28 w-28 rounded-full bg-white/10 blur-2xl"></div>
                                        <span class="inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white/15 text-white shadow-inner shadow-amber-950/10">
                                            <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M20 12H4"/>
                                            </svg>
                                        </span>
                                        <div class="relative mt-6">
                                            <div class="flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="text-xl font-bold">ถอน</div>
                                                    <p class="mt-2 text-sm leading-6 text-amber-50/90">
                                                        ดำเนินการถอนยอดสะสมของสมาชิก พร้อมตรวจสอบข้อมูลก่อนยืนยันรายการ
                                                    </p>
                                                </div>
                                                <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-semibold text-white">เมนูหลัก</span>
                                            </div>
                                            <div class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-white">
                                                เปิดหน้าถอน
                                                <svg class="h-4 w-4 transition group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <section>
                                    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                                        <div>
                                            <h2 class="text-xl font-bold text-emerald-950">เมนูจัดการระบบ</h2>
                                            <p class="mt-1 text-sm text-emerald-800/75">รวมหน้าจัดการข้อมูลและติดตามผลการทำงานในที่เดียว</p>
                                        </div>
                                        <span class="inline-flex items-center rounded-full border border-emerald-200 bg-white px-3 py-1 text-xs font-semibold text-emerald-700">
                                            {{ $privilegedMenuCount - 2 }} เมนูเสริม
                                        </span>
                                    </div>

                                    <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                                        <a href="{{ route('transactions.index') }}" class="group rounded-[26px] border border-slate-200/80 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-1 hover:border-blue-200 hover:shadow-[0_20px_40px_rgba(59,130,246,0.12)]">
                                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-100 text-blue-700">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                                </svg>
                                            </span>
                                            <div class="mt-4 flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="text-lg font-bold text-slate-900">ประวัติรายการ</div>
                                                    <p class="mt-2 text-sm leading-6 text-slate-600">ค้นหาและตรวจสอบธุรกรรมย้อนหลังของแต่ละครัวเรือน</p>
                                                </div>
                                                <svg class="mt-1 h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                        </a>

                                        <a href="{{ route('reports.index') }}" class="group rounded-[26px] border border-slate-200/80 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-1 hover:border-cyan-200 hover:shadow-[0_20px_40px_rgba(6,182,212,0.12)]">
                                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M4 19h16M7 16V9m5 7V5m5 11v-4"/>
                                                </svg>
                                            </span>
                                            <div class="mt-4 flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="text-lg font-bold text-slate-900">สรุปรายงาน</div>
                                                    <p class="mt-2 text-sm leading-6 text-slate-600">ดูภาพรวมยอดรับซื้อ ถอน และส่งออกเอกสารรายงาน</p>
                                                </div>
                                                <svg class="mt-1 h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                        </a>

                                        <a href="{{ route('households.index') }}" class="group rounded-[26px] border border-slate-200/80 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-1 hover:border-indigo-200 hover:shadow-[0_20px_40px_rgba(99,102,241,0.12)]">
                                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M3 10.5L12 4l9 6.5v8a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1v-8z"/>
                                                </svg>
                                            </span>
                                            <div class="mt-4 flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="text-lg font-bold text-slate-900">ครัวเรือน</div>
                                                    <p class="mt-2 text-sm leading-6 text-slate-600">จัดการข้อมูลสมาชิกครัวเรือนและตรวจสอบรายการของแต่ละบัญชี</p>
                                                </div>
                                                <svg class="mt-1 h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                        </a>

                                        <a href="{{ route('materials.index') }}" class="group rounded-[26px] border border-slate-200/80 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-1 hover:border-teal-200 hover:shadow-[0_20px_40px_rgba(20,184,166,0.12)]">
                                            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-teal-100 text-teal-700">
                                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                                </svg>
                                            </span>
                                            <div class="mt-4 flex items-start justify-between gap-3">
                                                <div>
                                                    <div class="text-lg font-bold text-slate-900">วัสดุ</div>
                                                    <p class="mt-2 text-sm leading-6 text-slate-600">รวมการจัดการวัสดุ หมวดหมู่ และราคาไว้ในจุดเดียว</p>
                                                </div>
                                                <svg class="mt-1 h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-teal-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </div>
                                        </a>

                                        @if($authUser?->role === 'admin')
                                            <a href="{{ route('admin.users.index') }}" class="group rounded-[26px] border border-slate-200/80 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-1 hover:border-sky-200 hover:shadow-[0_20px_40px_rgba(14,165,233,0.12)]">
                                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M17 20h5V9l-5-4M17 20H7m10 0v-6a2 2 0 00-2-2h-2a2 2 0 00-2 2v6M7 20H2V5a2 2 0 012-2h8l5 4M9 9h1m4 0h1m-6 4h6"/>
                                                    </svg>
                                                </span>
                                                <div class="mt-4 flex items-start justify-between gap-3">
                                                    <div>
                                                        <div class="text-lg font-bold text-slate-900">บัญชีผู้ใช้ทั้งหมด</div>
                                                        <p class="mt-2 text-sm leading-6 text-slate-600">ดูรายชื่อผู้ใช้ทุก role พร้อมสถานะ การเข้าใช้ล่าสุด และลิงก์ไปยัง log ของแต่ละบัญชี</p>
                                                    </div>
                                                    <svg class="mt-1 h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </div>
                                            </a>

                                            <a href="{{ route('admin.activity-logs.index') }}" class="group rounded-[26px] border border-slate-200/80 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-1 hover:border-amber-200 hover:shadow-[0_20px_40px_rgba(245,158,11,0.12)]">
                                                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                </span>
                                                <div class="mt-4 flex items-start justify-between gap-3">
                                                    <div>
                                                        <div class="text-lg font-bold text-slate-900">Activity Log</div>
                                                        <p class="mt-2 text-sm leading-6 text-slate-600">ตรวจสอบได้ว่าใครเข้าสู่ระบบ สร้าง แก้ไข หรือลบข้อมูลอะไรในระบบบ้าง</p>
                                                    </div>
                                                    <svg class="mt-1 h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </div>
                                            </a>
                                        @endif
                                    </div>
                                </section>
                            </div>

                            <div class="space-y-4">
                                <section class="rounded-[30px] border border-red-100 bg-[linear-gradient(180deg,rgba(254,242,242,0.96),rgba(255,255,255,0.98))] p-5 shadow-[0_12px_30px_rgba(248,113,113,0.08)]">
                                    <div class="text-sm font-semibold text-slate-900">บัญชีผู้ใช้งาน</div>
                                    <div class="mt-2 text-sm leading-6 text-slate-600">
                                        กำลังเข้าสู่ระบบด้วยบัญชี <span class="font-semibold text-slate-900">{{ $authUser->username }}</span>
                                        ในสิทธิ์ <span class="font-semibold text-slate-900">{{ $roleLabel }}</span>
                                    </div>
                                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                                        @csrf
                                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl border border-red-200 bg-white px-4 py-3 text-sm font-semibold text-red-700 transition hover:border-red-300 hover:bg-red-50">
                                            ออกจากระบบ
                                        </button>
                                    </form>
                                </section>

                                <section class="rounded-[30px] border border-emerald-100 bg-[linear-gradient(180deg,rgba(236,253,245,0.96),rgba(255,255,255,0.98))] p-5 shadow-[inset_0_1px_0_rgba(255,255,255,0.7)]">
                                    <div class="flex items-center justify-between gap-3">
                                        <div>
                                            <div class="text-sm font-semibold text-emerald-900">ภาพรวมพร้อมใช้งาน</div>
                                            <p class="mt-1 text-sm text-emerald-700/75">ตัวเลขสรุปสำหรับการเข้าถึงเมนูและข้อมูลในระบบ</p>
                                        </div>
                                        <div class="rounded-2xl bg-white/90 px-3 py-2 text-xs font-semibold text-emerald-700 shadow-sm">
                                            วันนี้
                                        </div>
                                    </div>

                                    <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-1">
                                        <div class="rounded-2xl border border-white/80 bg-white/90 p-4 shadow-sm">
                                            <div class="text-xs font-semibold tracking-[0.16em] text-emerald-600">เมนูหลัก</div>
                                            <div class="mt-2 text-3xl font-bold text-emerald-950">{{ $privilegedMenuCount }}</div>
                                            <div class="mt-1 text-sm text-emerald-700/75">พร้อมใช้งานครบทุกส่วน</div>
                                        </div>
                                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-2">
                                            <div class="rounded-2xl border border-white/80 bg-white/90 p-4 shadow-sm">
                                                <div class="text-xs font-semibold tracking-[0.16em] text-emerald-600">วัสดุ</div>
                                                <div class="mt-2 text-2xl font-bold text-emerald-950">{{ $totalMaterials }}</div>
                                                <div class="mt-1 text-sm text-emerald-700/75">รายการในระบบ</div>
                                            </div>
                                            <div class="rounded-2xl border border-white/80 bg-white/90 p-4 shadow-sm">
                                                <div class="text-xs font-semibold tracking-[0.16em] text-emerald-600">หมวดหมู่</div>
                                                <div class="mt-2 text-2xl font-bold text-emerald-950">{{ $totalCategories }}</div>
                                                <div class="mt-1 text-sm text-emerald-700/75">หมวดวัสดุทั้งหมด</div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                                <section class="rounded-[30px] border border-slate-200/80 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.06)]">
                                    <div class="text-sm font-semibold text-slate-900">ลำดับงานแนะนำ</div>
                                    <div class="mt-4 space-y-3">
                                        <div class="flex gap-3 rounded-2xl bg-slate-50 p-4">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-sm font-bold text-white">1</div>
                                            <div>
                                                <div class="text-sm font-semibold text-slate-900">เริ่มงานหน้าจุดรับซื้อหรือถอน</div>
                                                <div class="mt-1 text-sm leading-6 text-slate-600">ใช้เมนูใหญ่ด้านซ้ายเพื่อบันทึกรายการประจำวันได้ทันที</div>
                                            </div>
                                        </div>
                                        <div class="flex gap-3 rounded-2xl bg-slate-50 p-4">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-cyan-600 text-sm font-bold text-white">2</div>
                                            <div>
                                                <div class="text-sm font-semibold text-slate-900">ตรวจสอบประวัติและสรุปรายงาน</div>
                                                <div class="mt-1 text-sm leading-6 text-slate-600">ย้อนดูธุรกรรม รายการผิดปกติ และสรุปผลการดำเนินงานจากเมนูรายงาน</div>
                                            </div>
                                        </div>
                                        <div class="flex gap-3 rounded-2xl bg-slate-50 p-4">
                                            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-500 text-sm font-bold text-white">3</div>
                                            <div>
                                                <div class="text-sm font-semibold text-slate-900">จัดการหน้า วัสดุ</div>
                                                <div class="mt-1 text-sm leading-6 text-slate-600">เข้าเมนูเดียวเพื่อดูแลรายการวัสดุ หมวดหมู่ และราคาให้พร้อมใช้งาน</div>
                                            </div>
                                        </div>
                                    </div>
                                </section>

                            </div>
                        </div>
                    </div>
                </section>
            @else
                <div class="{{ $authUser ? 'lg:flex lg:gap-8' : '' }}">
                    @auth
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
                                        <span>อัปเดตล่าสุด {{ now()->format('d/m/Y H:i') }} น.</span>
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
                    @endauth

                    <main class="flex-1">
                        <div
                            x-data='{
                                activeCategory: "all",
                                categoryFilters: @json($categoryFilters),
                                totalMaterials: {{ $totalMaterials }},
                                get activeCategoryLabel() {
                                    if (this.activeCategory === "all") {
                                        return "ทั้งหมด";
                                    }

                                    const selected = this.categoryFilters.find((filter) => filter.name === this.activeCategory);

                                    return selected ? selected.name : "ทั้งหมด";
                                },
                                get visibleCount() {
                                    if (this.activeCategory === "all") {
                                        return this.totalMaterials;
                                    }

                                    const selected = this.categoryFilters.find((filter) => filter.name === this.activeCategory);

                                    return selected ? selected.count : this.totalMaterials;
                                }
                            }'
                            class="overflow-hidden rounded-3xl border border-emerald-100 bg-white/90 shadow-sm"
                        >
                            <div class="border-b border-emerald-100 p-4 sm:p-6">
                                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <div class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            {{ $authUser ? 'ข้อมูลราคาสำหรับใช้งานประจำวัน' : 'เปิดให้ดูราคาโดยไม่ต้องเข้าสู่ระบบ' }}
                                        </div>
                                        <h2 class="mt-3 text-2xl font-bold text-emerald-900 sm:text-3xl">รายการวัสดุและราคา</h2>
                                        <p class="mt-1 text-sm text-emerald-700/70">แสดงราคาปัจจุบันที่มีผลใช้งาน</p>
                                    </div>

                                    @if(!$authUser)
                                        <div class="flex flex-wrap items-center gap-2">
                                            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                                                สมัครสมาชิก
                                            </a>
                                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:border-emerald-300 hover:text-emerald-800">
                                                ลงชื่อเข้าใช้
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="p-4 sm:p-6">
                                @if($totalCategories > 1)
                                    <div class="mb-6 rounded-3xl border border-emerald-100 bg-[linear-gradient(135deg,rgba(236,253,245,0.96),rgba(255,255,255,0.98))] p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.7)]">
                                        <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                            <div>
                                                <div class="text-sm font-semibold text-emerald-900">กรองตามหมวดหมู่</div>
                                                <p class="mt-1 text-xs text-emerald-700/80 sm:text-sm">
                                                    เลือกดูเฉพาะหมวดที่สนใจเพื่อไล่เช็กราคาได้ง่ายขึ้น
                                                </p>
                                            </div>

                                            <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-emerald-700/80">
                                                <span class="rounded-full border border-emerald-200 bg-white/80 px-3 py-1">
                                                    {{ $totalCategories }} หมวด
                                                </span>
                                                <span class="rounded-full border border-emerald-200 bg-white/80 px-3 py-1">
                                                    <span x-text="visibleCount"></span> รายการที่แสดง
                                                </span>
                                            </div>
                                        </div>

                                        <div class="mt-4 flex flex-wrap items-start gap-2">
                                            <button
                                                type="button"
                                                @click='activeCategory = "all"'
                                                :aria-pressed='activeCategory === "all"'
                                                :class='activeCategory === "all"
                                                    ? "border-emerald-600 bg-emerald-600 text-white shadow-sm shadow-emerald-200"
                                                    : "border-white/80 bg-white/90 text-emerald-800 hover:border-emerald-200 hover:bg-white"'
                                                class="inline-flex max-w-full flex-wrap items-start gap-2 rounded-full border px-3 py-2 text-left text-sm font-semibold leading-5 transition sm:flex-nowrap sm:items-center"
                                            >
                                                <span
                                                    :class='activeCategory === "all" ? "bg-white" : "bg-emerald-500"'
                                                    class="mt-1 h-2 w-2 shrink-0 rounded-full sm:mt-0"
                                                ></span>
                                                <span class="min-w-0 break-words">ทั้งหมด</span>
                                                <span
                                                    :class='activeCategory === "all" ? "bg-white/15 text-white" : "bg-emerald-50 text-emerald-700"'
                                                    class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold"
                                                >
                                                    {{ $totalMaterials }}
                                                </span>
                                            </button>

                                            @foreach($categoryFilters as $filter)
                                                <button
                                                    type="button"
                                                    @click='activeCategory = @json($filter["name"])'
                                                    :aria-pressed='activeCategory === @json($filter["name"])'
                                                    :class='activeCategory === @json($filter["name"])
                                                        ? "border-emerald-600 bg-emerald-600 text-white shadow-sm shadow-emerald-200"
                                                        : "border-white/80 bg-white/90 text-emerald-800 hover:border-emerald-200 hover:bg-white"'
                                                    class="inline-flex max-w-full flex-wrap items-start gap-2 rounded-full border px-3 py-2 text-left text-sm font-semibold leading-5 transition sm:flex-nowrap sm:items-center"
                                                >
                                                    <span
                                                        :class='activeCategory === @json($filter["name"]) ? "bg-white" : "bg-emerald-500"'
                                                        class="mt-1 h-2 w-2 shrink-0 rounded-full sm:mt-0"
                                                    ></span>
                                                    <span class="min-w-0 break-words">{{ $filter['name'] }}</span>
                                                    <span
                                                        :class='activeCategory === @json($filter["name"]) ? "bg-white/15 text-white" : "bg-emerald-50 text-emerald-700"'
                                                        class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold"
                                                    >
                                                        {{ $filter['count'] }}
                                                    </span>
                                                </button>
                                            @endforeach
                                        </div>

                                        <div class="mt-3 text-xs text-emerald-700/80">
                                            กำลังแสดง:
                                            <span class="font-semibold text-emerald-900" x-text="activeCategoryLabel"></span>
                                        </div>
                                    </div>
                                @endif

                                @forelse($grouped as $categoryName => $items)
                                    <div
                                        x-show='activeCategory === "all" || activeCategory === @json($categoryName)'
                                        x-transition.opacity.duration.200ms
                                        class="mb-6 last:mb-0"
                                    >
                                        <div class="flex items-center gap-2">
                                            <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                                            <h3 class="text-lg font-semibold break-words text-emerald-900">{{ $categoryName }}</h3>
                                            <span class="rounded-full border border-emerald-100 bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">
                                                {{ $items->count() }} รายการ
                                            </span>
                                        </div>

                                        <div class="mt-3 overflow-hidden rounded-2xl border border-emerald-200 bg-emerald-100 p-px shadow-sm">
                                            <div class="grid gap-px bg-emerald-100 sm:grid-cols-2 lg:grid-cols-3">
                                                @foreach($items as $m)
                                                    @php
                                                        $currentPrice = $m->prices->first();
                                                    @endphp
                                                    <div class="bg-white p-4">
                                                        <div class="text-base font-semibold break-words text-emerald-900">{{ $m->material_name }}</div>
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
            @endif
        </div>
    </div>
</x-app-layout>
