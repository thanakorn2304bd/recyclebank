<x-guest-layout>
    <div class="mb-6">
        <div class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
            ขั้นตอนที่ 2 จาก 2
        </div>
        <h1 class="mt-3 text-2xl font-bold text-emerald-900">ส่งเอกสารยืนยันตัวตน</h1>
        <p class="mt-2 text-sm text-gray-600">
            อัปโหลดเอกสารของสมาชิกแต่ละคนให้ครบทั้งสำเนาทะเบียนบ้านและสำเนาบัตรประชาชน เพื่อยืนยันชื่อในทะเบียนบ้านก่อนส่งคำขอสมัครสมาชิก
        </p>
    </div>

    @if ($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-1 ps-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="mb-6 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4">
        <div class="text-sm font-semibold text-emerald-900">สรุปข้อมูลก่อนส่งเอกสาร</div>
        <div class="mt-3 grid gap-3 text-sm text-emerald-900/90 sm:grid-cols-2">
            <div class="rounded-xl bg-white/90 px-4 py-3 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">เลขบัญชี</div>
                <div class="mt-1 font-semibold">{{ $registration['account_no'] ?? '-' }}</div>
            </div>
            <div class="rounded-xl bg-white/90 px-4 py-3 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">ชุมชน</div>
                <div class="mt-1 font-semibold">{{ $community?->community_name ? $community->community_id.' - '.$community->community_name : ($registration['form']['community_id'] ?? '-') }}</div>
            </div>
            <div class="rounded-xl bg-white/90 px-4 py-3 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">ผู้ติดต่อ</div>
                <div class="mt-1 font-semibold">{{ $registration['form']['contact_person'] ?? '-' }}</div>
            </div>
            <div class="rounded-xl bg-white/90 px-4 py-3 shadow-sm">
                <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">สมาชิกที่บันทึก</div>
                <div class="mt-1 font-semibold">{{ count(is_array($registration['members'] ?? null) ? $registration['members'] : []) }} คน</div>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('register.complete') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="draft_token" value="{{ $draftToken }}">

        <div class="space-y-4">
            <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <div class="block text-base font-semibold text-gray-900">เอกสารของสมาชิก</div>
                <p class="mt-1 text-sm text-gray-600">สมาชิกแต่ละคนต้องแนบ 2 ไฟล์: สำเนาทะเบียนบ้านและสำเนาบัตรประชาชน</p>
                <p class="mt-2 text-xs text-emerald-700">ต้องแนบทั้งหมด {{ count($registrationMembers) * 2 }} ไฟล์ ตามจำนวนสมาชิกที่บันทึกไว้</p>
                <x-input-error :messages="$errors->get('member_household_copies')" class="mt-2" />
                <x-input-error :messages="$errors->get('member_national_id_copies')" class="mt-2" />

                <div class="mt-4 space-y-4">
                    @foreach($registrationMembers as $index => $member)
                        <div class="rounded-2xl border border-emerald-100 bg-emerald-50/60 p-4">
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <div class="text-sm font-semibold text-emerald-950">
                                        สมาชิกคนที่ {{ $member['position'] }}: {{ $member['display_name'] }}
                                    </div>
                                    @if($member['display_meta'] !== '')
                                        <div class="mt-1 text-xs text-emerald-800/80">{{ $member['display_meta'] }}</div>
                                    @endif
                                </div>
                            </div>

                            <div class="mt-4 grid gap-4 lg:grid-cols-2">
                                <div>
                                    <label for="member_household_copies_{{ $index }}" class="block text-sm font-semibold text-gray-900">
                                        {{ $documentRequirements['member_household_copies']['label'] }}
                                    </label>
                                    <p class="mt-1 text-xs text-gray-600">{{ $documentRequirements['member_household_copies']['description'] }}</p>

                                    <input
                                        id="member_household_copies_{{ $index }}"
                                        name="member_household_copies[{{ $index }}]"
                                        type="file"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        class="mt-3 block w-full rounded-xl border border-gray-300 bg-white px-3 py-3 text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:font-semibold file:text-white hover:file:bg-emerald-700"
                                        required
                                    >

                                    <p class="mt-2 text-xs text-gray-500">รองรับไฟล์ JPG, PNG หรือ PDF ขนาดไม่เกิน 5 MB</p>
                                    <x-input-error :messages="$errors->get('member_household_copies.'.$index)" class="mt-2" />
                                </div>

                                <div>
                                    <label for="member_national_id_copies_{{ $index }}" class="block text-sm font-semibold text-gray-900">
                                        {{ $documentRequirements['member_national_id_copies']['label'] }}
                                    </label>
                                    <p class="mt-1 text-xs text-gray-600">{{ $documentRequirements['member_national_id_copies']['description'] }}</p>

                                    <input
                                        id="member_national_id_copies_{{ $index }}"
                                        name="member_national_id_copies[{{ $index }}]"
                                        type="file"
                                        accept=".jpg,.jpeg,.png,.pdf"
                                        class="mt-3 block w-full rounded-xl border border-gray-300 bg-white px-3 py-3 text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:font-semibold file:text-white hover:file:bg-emerald-700"
                                        required
                                    >

                                    <p class="mt-2 text-xs text-gray-500">รองรับไฟล์ JPG, PNG หรือ PDF ขนาดไม่เกิน 5 MB</p>
                                    <x-input-error :messages="$errors->get('member_national_id_copies.'.$index)" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            เมื่อกดส่งคำขอ ระบบจะบันทึกข้อมูลครัวเรือน บัญชีสมาชิก และเอกสารทะเบียนบ้านพร้อมบัตรประชาชนของสมาชิกแต่ละคนในสถานะรอเจ้าหน้าที่ยืนยัน
        </div>

        <div class="mt-6 flex items-center justify-between gap-3">
            <a
                href="{{ route('register', array_filter(['draft' => $draftToken])) }}"
                class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
            >
                ย้อนกลับไปแก้ข้อมูล
            </a>

            <x-primary-button>
                ส่งคำขอสมัครสมาชิก
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
