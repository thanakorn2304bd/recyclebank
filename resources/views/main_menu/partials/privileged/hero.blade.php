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
                    อัปเดต {{ $updatedAtLabel }} น.
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
