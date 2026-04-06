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
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-2xl bg-emerald-600 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-emerald-200 transition hover:-translate-y-0.5 hover:bg-emerald-700">
                    ลงชื่อเข้าใช้
                </a>
                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-2xl border border-emerald-200 bg-white px-5 py-3 text-sm font-semibold text-emerald-800 transition hover:border-emerald-300 hover:bg-emerald-50">
                    สมัครสมาชิก
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
                อัปเดตราคา ณ {{ $todayLabel }} และเปิดให้สมัครสมาชิกใหม่ได้ตลอดเวลา
            </div>
        </div>
    </div>
</section>
