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
            {{ $managementMenuCount }} เมนูเสริม
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

        <a href="{{ route('withdraw-requests.index') }}" class="group rounded-[26px] border border-slate-200/80 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-1 hover:border-amber-200 hover:shadow-[0_20px_40px_rgba(245,158,11,0.12)]">
            <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-100 text-amber-700">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M8 7V5a4 4 0 118 0v2m-9 0h10a2 2 0 012 2v9a2 2 0 01-2 2H7a2 2 0 01-2-2V9a2 2 0 012-2zm5 4v4m0 0l-2-2m2 2l2-2"/>
                </svg>
            </span>
            <div class="mt-4 flex items-start justify-between gap-3">
                <div>
                    <div class="text-lg font-bold text-slate-900">คำขอถอน</div>
                    <p class="mt-2 text-sm leading-6 text-slate-600">ตรวจสอบคำขอถอนที่สมาชิกยื่นเอง พิมพ์แบบฟอร์ม และอนุมัติให้กลายเป็นรายการถอนจริง</p>
                </div>
                <svg class="mt-1 h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

        @if(config('features.pdpa', false))
            <a href="{{ route('compliance.dsars.index') }}" class="group rounded-[26px] border border-slate-200/80 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-1 hover:border-rose-200 hover:shadow-[0_20px_40px_rgba(244,63,94,0.12)]">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-rose-100 text-rose-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 12h6m-6 4h4m5-8V7a2 2 0 00-2-2H8a2 2 0 00-2 2v10a2 2 0 002 2h4m6-11l3 3m0 0l-3 3m3-3H12"/>
                    </svg>
                </span>
                <div class="mt-4 flex items-start justify-between gap-3">
                    <div>
                        <div class="text-lg font-bold text-slate-900">PDPA / Compliance</div>
                        <p class="mt-2 text-sm leading-6 text-slate-600">ติดตามคำขอเจ้าของข้อมูลและเหตุการณ์ข้อมูลส่วนบุคคลเพื่อให้การทำงานสอดคล้องกับ PDPA</p>
                    </div>
                    <svg class="mt-1 h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        @endif

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

            <a href="{{ route('admin.communities.index') }}" class="group rounded-[26px] border border-slate-200/80 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-[0_20px_40px_rgba(16,185,129,0.12)]">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M17 20h5V9l-5-4M17 20H7m10 0v-6a2 2 0 00-2-2h-2a2 2 0 00-2 2v6M7 20H2V5a2 2 0 012-2h8l5 4M9 9h1m4 0h1m-6 4h6"/>
                    </svg>
                </span>
                <div class="mt-4 flex items-start justify-between gap-3">
                    <div>
                        <div class="text-lg font-bold text-slate-900">ชุมชน</div>
                        <p class="mt-2 text-sm leading-6 text-slate-600">จัดการข้อมูลชุมชนที่ใช้เป็นข้อมูลพื้นฐานในการลงทะเบียนครัวเรือน</p>
                    </div>
                    <svg class="mt-1 h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <a href="{{ route('admin.backup.index') }}" class="group rounded-[26px] border border-slate-200/80 bg-white p-5 shadow-[0_12px_30px_rgba(15,23,42,0.06)] transition duration-200 hover:-translate-y-1 hover:border-violet-200 hover:shadow-[0_20px_40px_rgba(139,92,246,0.12)]">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-100 text-violet-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M4 7v10a2 2 0 002 2h12a2 2 0 002-2V7M4 7l8-4 8 4M4 7h16M9 12h6"/>
                    </svg>
                </span>
                <div class="mt-4 flex items-start justify-between gap-3">
                    <div>
                        <div class="text-lg font-bold text-slate-900">Backup ฐานข้อมูล</div>
                        <p class="mt-2 text-sm leading-6 text-slate-600">สร้างและดาวน์โหลด SQL dump ของฐานข้อมูลทั้งหมด เพื่อสำรองข้อมูลก่อนทำการเปลี่ยนแปลงสำคัญ</p>
                    </div>
                    <svg class="mt-1 h-5 w-5 text-slate-300 transition group-hover:translate-x-1 group-hover:text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.4" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        @endif
    </div>
</section>
