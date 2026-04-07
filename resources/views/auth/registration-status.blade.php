<x-guest-layout max-width="6xl" panel-class="px-5 py-6 sm:px-8 lg:px-10">
    @if (session('status'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
            {{ session('status') }}
        </div>
    @endif

    <div class="space-y-8">
            <section class="overflow-hidden rounded-[2rem] border border-emerald-200 bg-gradient-to-br from-emerald-50 via-white to-cyan-50">
                <div class="px-6 py-7 sm:px-8 lg:px-10">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start xl:justify-between">
                        <div class="max-w-3xl">
                            <div class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusSummary['badge_class'] }}">
                                {{ $statusSummary['badge'] }}
                            </div>
                            <h1 class="mt-4 text-3xl font-black tracking-tight text-emerald-950 sm:text-4xl">
                                ติดตามผลคำขอสมัครสมาชิก
                            </h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-gray-700 sm:text-base">
                                {{ $statusSummary['description'] }}
                            </p>
                        </div>

                        <form method="POST" action="{{ route('registration-status.logout') }}">
                            @csrf
                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50"
                            >
                                เปลี่ยนบัญชี
                            </button>
                        </form>
                    </div>

                    <div class="mt-7 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div class="rounded-2xl border border-white/80 bg-white/90 px-5 py-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">เลขบัญชี</div>
                            <div class="mt-2 text-xl font-bold text-emerald-950">{{ $trackedHousehold->account_no }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/80 bg-white/90 px-5 py-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">ผู้ติดต่อ</div>
                            <div class="mt-2 text-lg font-bold text-emerald-950">{{ $trackedHousehold->contact_person }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/80 bg-white/90 px-5 py-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">วันที่สมัคร</div>
                            <div class="mt-2 text-lg font-bold text-emerald-950">{{ $trackedHousehold->register_date?->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div class="rounded-2xl border border-white/80 bg-white/90 px-5 py-4 shadow-sm">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-600">สมาชิกในคำขอ</div>
                            <div class="mt-2 text-lg font-bold text-emerald-950">{{ count($trackingMembers) }} คน</div>
                        </div>
                    </div>
                </div>
            </section>

            @if ($errors->any())
                <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                    <ul class="list-disc space-y-1 ps-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid gap-6 xl:grid-cols-[1.2fr,0.8fr]">
                <section class="rounded-[2rem] border border-gray-200 bg-white p-6 shadow-sm lg:p-8">
                    <h2 class="text-xl font-bold text-gray-950">ผลการพิจารณาล่าสุด</h2>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl bg-gray-50 px-5 py-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">ผู้พิจารณา</div>
                            <div class="mt-2 text-base font-bold text-gray-900">
                                {{ $trackedHousehold->reviewedByUser?->staff?->full_name ?? $trackedHousehold->reviewedByUser?->username ?? '-' }}
                            </div>
                        </div>
                        <div class="rounded-2xl bg-gray-50 px-5 py-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">เวลาพิจารณา</div>
                            <div class="mt-2 text-base font-bold text-gray-900">{{ $trackedHousehold->reviewed_at?->format('d/m/Y H:i') ?? '-' }}</div>
                        </div>
                    </div>

                    <div class="mt-5 rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4">
                        <div class="flex items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-gray-950">หมายเหตุจากเจ้าหน้าที่</div>
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $statusSummary['badge_class'] }}">
                                {{ $statusSummary['badge'] }}
                            </span>
                        </div>
                        <p class="mt-3 text-sm leading-7 text-gray-700">
                            {{ $trackedHousehold->review_notes ?: 'ยังไม่มีหมายเหตุเพิ่มเติมจากเจ้าหน้าที่ในขณะนี้' }}
                        </p>
                    </div>
                </section>

                <section class="rounded-[2rem] border border-gray-200 bg-white p-6 shadow-sm lg:p-8">
                    <h2 class="text-xl font-bold text-gray-950">ขั้นตอนถัดไป</h2>

                    @if ($trackedHousehold->active_status === 'inactive')
                        <div class="mt-5 rounded-2xl border border-gray-200 bg-gray-50 px-5 py-4 text-sm leading-7 text-gray-700">
                            บัญชีนี้ถูกปิดการใช้งาน กรุณาติดต่อเจ้าหน้าที่เพื่อสอบถามข้อมูลเพิ่มเติม
                        </div>
                    @elseif ($trackedHousehold->active_status === 'rejected')
                        <div class="mt-5 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm leading-7 text-red-800">
                            คำขอสมัครสมาชิกไม่ผ่านการอนุมัติ คุณสามารถอัปโหลดเอกสารชุดใหม่ด้านล่างเพื่อขอรับการพิจารณาอีกครั้ง
                        </div>
                    @elseif ($trackedHousehold->active_status === 'pending')
                        <div class="mt-5 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm leading-7 text-amber-900">
                            ตอนนี้คำขออยู่ระหว่างตรวจสอบจากเจ้าหน้าที่ ยังไม่ต้องส่งเอกสารซ้ำ เว้นแต่เจ้าหน้าที่จะตีกลับพร้อมหมายเหตุ
                        </div>
                    @else
                        <div class="mt-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm leading-7 text-emerald-800">
                            บัญชีนี้ได้รับการอนุมัติแล้ว สามารถกลับไปเข้าสู่ระบบตามปกติได้เลย
                        </div>
                    @endif

                    <div class="mt-5 space-y-3">
                        <div class="rounded-2xl bg-gray-50 px-5 py-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">สถานะปัจจุบัน</div>
                            <div class="mt-2 text-base font-bold text-gray-900">{{ $statusSummary['title'] }}</div>
                        </div>
                        <div class="rounded-2xl bg-gray-50 px-5 py-4">
                            <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">การเข้าสู่ระบบ</div>
                            <div class="mt-2 text-sm leading-7 text-gray-700">
                                ใช้เลขบัญชีและรหัสผ่านเดิมได้เหมือนเดิม เมื่อได้รับอนุมัติแล้วจึงจะเข้าสู่ระบบหลักได้
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <section class="rounded-[2rem] border border-gray-200 bg-white p-6 shadow-sm lg:p-8">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-950">เอกสารล่าสุดที่ระบบได้รับ</h2>
                        <p class="mt-2 text-sm leading-7 text-gray-600">
                            แสดงเอกสารที่ผูกกับสมาชิกแต่ละคน เพื่อให้ตรวจสอบได้ง่ายก่อนส่งแก้ไข
                        </p>
                    </div>
                </div>

                <div class="mt-6 grid gap-4 lg:grid-cols-2">
                    @foreach ($trackingMembers as $member)
                        @php
                            $householdCopy = $documentLookup->get($member['position'].'-household_copy');
                            $nationalIdCopy = $documentLookup->get($member['position'].'-national_id_copy');
                        @endphp
                        <div class="rounded-[1.5rem] border border-emerald-100 bg-emerald-50/60 p-5">
                            <div class="text-base font-bold text-emerald-950">สมาชิกคนที่ {{ $member['position'] }}: {{ $member['display_name'] }}</div>
                            @if ($member['display_meta'] !== '')
                                <div class="mt-1 text-sm text-emerald-800/80">{{ $member['display_meta'] }}</div>
                            @endif

                            <div class="mt-5 grid gap-3">
                                <div class="rounded-2xl bg-white px-4 py-4 shadow-sm">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">{{ $documentRequirements['member_household_copies']['label'] }}</div>
                                    <div class="mt-2 text-sm font-semibold text-gray-900">{{ $householdCopy?->original_name ?? 'ยังไม่มีข้อมูล' }}</div>
                                </div>
                                <div class="rounded-2xl bg-white px-4 py-4 shadow-sm">
                                    <div class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-500">{{ $documentRequirements['member_national_id_copies']['label'] }}</div>
                                    <div class="mt-2 text-sm font-semibold text-gray-900">{{ $nationalIdCopy?->original_name ?? 'ยังไม่มีข้อมูล' }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            @if ($trackedHousehold->active_status === 'rejected')
                <form method="POST" action="{{ route('registration-status.documents.update') }}" enctype="multipart/form-data" class="rounded-[2rem] border border-rose-200 bg-white p-6 shadow-sm lg:p-8">
                    @csrf
                    @method('PATCH')

                    <div class="flex flex-col gap-3 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-rose-950">อัปโหลดเอกสารแก้ไข</h2>
                            <p class="mt-2 text-sm leading-7 text-gray-600">
                                แนบเอกสารใหม่ให้ครบทุกสมาชิก ระบบจะบันทึกแทนชุดเดิมทั้งหมดแล้วส่งคำขอกลับเข้าคิวตรวจสอบ
                            </p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 xl:grid-cols-2">
                        @foreach ($trackingMembers as $index => $member)
                            <div class="rounded-[1.5rem] border border-rose-200 bg-rose-50/50 p-5">
                                <div class="text-base font-bold text-gray-950">สมาชิกคนที่ {{ $member['position'] }}: {{ $member['display_name'] }}</div>
                                @if ($member['display_meta'] !== '')
                                    <div class="mt-1 text-sm text-gray-600">{{ $member['display_meta'] }}</div>
                                @endif

                                <div class="mt-5 space-y-5">
                                    <div>
                                        <label for="member_household_copies_{{ $index }}" class="block text-sm font-semibold text-gray-900">
                                            {{ $documentRequirements['member_household_copies']['label'] }}
                                        </label>
                                        <input
                                            id="member_household_copies_{{ $index }}"
                                            name="member_household_copies[{{ $index }}]"
                                            type="file"
                                            accept=".jpg,.jpeg,.png,.pdf"
                                            class="mt-3 block w-full rounded-2xl border border-gray-300 bg-white px-3 py-3 text-sm text-gray-700 file:mr-4 file:rounded-xl file:border-0 file:bg-rose-600 file:px-4 file:py-2.5 file:font-semibold file:text-white hover:file:bg-rose-700"
                                            required
                                        >
                                        <p class="mt-2 text-xs text-gray-500">รองรับไฟล์ JPG, PNG หรือ PDF ขนาดไม่เกิน 5 MB</p>
                                        <x-input-error :messages="$errors->get('member_household_copies.'.$index)" class="mt-2" />
                                    </div>

                                    <div>
                                        <label for="member_national_id_copies_{{ $index }}" class="block text-sm font-semibold text-gray-900">
                                            {{ $documentRequirements['member_national_id_copies']['label'] }}
                                        </label>
                                        <input
                                            id="member_national_id_copies_{{ $index }}"
                                            name="member_national_id_copies[{{ $index }}]"
                                            type="file"
                                            accept=".jpg,.jpeg,.png,.pdf"
                                            class="mt-3 block w-full rounded-2xl border border-gray-300 bg-white px-3 py-3 text-sm text-gray-700 file:mr-4 file:rounded-xl file:border-0 file:bg-rose-600 file:px-4 file:py-2.5 file:font-semibold file:text-white hover:file:bg-rose-700"
                                            required
                                        >
                                        <p class="mt-2 text-xs text-gray-500">รองรับไฟล์ JPG, PNG หรือ PDF ขนาดไม่เกิน 5 MB</p>
                                        <x-input-error :messages="$errors->get('member_national_id_copies.'.$index)" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex items-center justify-end">
                        <button
                            type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-rose-700"
                        >
                            ส่งเอกสารแก้ไขกลับไปพิจารณา
                        </button>
                    </div>
                </form>
            @endif
    </div>
</x-guest-layout>
