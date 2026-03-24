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
