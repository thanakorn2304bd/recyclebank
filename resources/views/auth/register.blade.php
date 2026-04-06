<x-guest-layout>
    <div class="mb-6">
        <div class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
            ขั้นตอนที่ 1 จาก 2
        </div>
        <h1 class="mt-3 text-2xl font-bold text-emerald-900">สมัครสมาชิกครัวเรือน</h1>
        <p class="mt-2 text-sm text-gray-600">
            กรอกข้อมูลครัวเรือนก่อน จากนั้นระบบจะพาไปหน้าอัปโหลดเอกสารยืนยันตัวตนเพื่อส่งคำขอสมัครเข้าใช้งาน
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

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="account_no" value="เลขบัญชี" />
            <x-text-input id="account_no" class="block mt-1 w-full bg-gray-100 text-gray-700" type="text" value="{{ old('account_no', $draftAccountNo) }}" maxlength="10" inputmode="numeric" readonly />
            <x-input-error :messages="$errors->get('account_no')" class="mt-2" />
            <p class="mt-2 text-sm text-gray-500">ระบบสร้างอัตโนมัติจากปีปัจจุบัน + ชุมชน + บ้านเลขที่ และให้ staff/admin แก้ไขได้ภายหลังเท่านั้น</p>
        </div>

        <div class="mt-4">
            <x-input-label for="community_id" value="เลขที่ชุมชน" />
            <select id="community_id" name="community_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required autofocus>
                <option value="">-- เลือกชุมชน --</option>
                @foreach($communities as $community)
                    <option value="{{ $community->community_id }}" @selected(old('community_id', $draftForm['community_id'] ?? '') == $community->community_id)>
                        {{ $community->community_id }} - {{ $community->community_name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('community_id')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="house_no" value="บ้านเลขที่" />
            <x-text-input id="house_no" class="block mt-1 w-full" type="text" name="house_no" :value="old('house_no', $draftForm['house_no'] ?? '')" required />
            <x-input-error :messages="$errors->get('house_no')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="village_no" value="หมู่" />
            <x-text-input id="village_no" class="block mt-1 w-full" type="text" name="village_no" :value="old('village_no', $draftForm['village_no'] ?? '')" />
            <x-input-error :messages="$errors->get('village_no')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="contact_person" value="ชื่อผู้ติดต่อ" />
            <x-text-input id="contact_person" class="block mt-1 w-full" type="text" name="contact_person" :value="old('contact_person', $draftForm['contact_person'] ?? '')" required />
            <x-input-error :messages="$errors->get('contact_person')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="phone" value="เบอร์โทร" />
            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone', $draftForm['phone'] ?? '')" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-emerald-900">สมาชิกในครัวเรือน</h2>
                    <p class="mt-1 text-sm text-emerald-800/75">ต้องมีสมาชิกอย่างน้อย 1 คน โดยระบบจะใช้ชื่อผู้ติดต่อเป็นค่าเริ่มต้นของคนแรก และยังแก้ไขได้</p>
                </div>
                <button
                    type="button"
                    id="addHouseholdMemberBtn"
                    class="inline-flex items-center justify-center rounded-xl bg-white px-4 py-2 text-sm font-semibold text-emerald-700 shadow-sm ring-1 ring-emerald-200 transition hover:bg-emerald-100"
                >
                    เพิ่มสมาชิกครัวเรือน
                </button>
            </div>

            <div id="householdMembersEmpty" class="mt-4 rounded-xl border border-dashed border-emerald-200 bg-white/80 px-4 py-6 text-center text-sm text-emerald-700/70">
                ระบบจะเตรียมสมาชิกคนแรกให้อัตโนมัติจากชื่อผู้ติดต่อ
            </div>

            <div id="householdMembersContainer" class="mt-4 space-y-3"></div>
        </div>

        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            หลังสมัคร ระบบจะสร้างบัญชีครัวเรือนในสถานะรออนุมัติ และจะยังไม่สามารถเข้าสู่ระบบได้จนกว่า staff/admin จะยืนยัน
        </div>

        <div class="mt-4 rounded-2xl border border-sky-200 bg-sky-50/90 p-4">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-sky-950">ประกาศคุ้มครองข้อมูลส่วนบุคคล</h2>
                    <p class="mt-1 text-sm text-sky-900/75">
                        {{ $privacyNotice?->summary }}
                    </p>
                    <p class="mt-2 text-xs text-sky-900/60">
                        เวอร์ชัน {{ $privacyNotice?->version_code ?? '-' }}
                        @if($privacyNotice?->effective_at)
                            | มีผลตั้งแต่ {{ $privacyNotice->effective_at->format('d/m/Y H:i') }}
                        @endif
                    </p>
                </div>
                <a
                    href="{{ route('privacy-notice.show') }}"
                    target="_blank"
                    class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-white px-4 py-2 text-sm font-semibold text-sky-700 shadow-sm transition hover:bg-sky-100"
                >
                    อ่านฉบับเต็ม
                </a>
            </div>

            <label class="mt-4 inline-flex items-start gap-3 text-sm text-sky-950">
                <input
                    type="checkbox"
                    name="accepted_privacy_notice"
                    value="1"
                    @checked(old('accepted_privacy_notice', $draftForm['accepted_privacy_notice'] ?? false))
                    class="mt-1 rounded border-sky-300 text-sky-600 shadow-sm focus:ring-sky-500"
                    required
                >
                <span>ข้าพเจ้ารับทราบประกาศคุ้มครองข้อมูลส่วนบุคคลและยินยอมให้ระบบเก็บ ใช้ และเปิดเผยข้อมูลเท่าที่จำเป็นเพื่อการสมัครสมาชิก การอนุมัติบัญชี การทำธุรกรรม และการตรวจสอบย้อนหลัง</span>
            </label>
            <x-input-error :messages="$errors->get('accepted_privacy_notice')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="รหัสผ่าน" />

            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pr-20"
                                type="password"
                                name="password"
                                required autocomplete="new-password" />

                <button
                    type="button"
                    id="toggleRegisterPasswordVisibility"
                    class="absolute inset-y-0 right-0 flex items-center rounded-r-md px-4 text-sm font-medium text-emerald-700 transition hover:text-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                    aria-controls="password"
                    aria-label="แสดงรหัสผ่าน"
                    aria-pressed="false"
                >
                    แสดง
                </button>
            </div>

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" value="ยืนยันรหัสผ่าน" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-emerald-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500" href="{{ route('login') }}">
                มีบัญชีอยู่แล้ว? ลงชื่อเข้าใช้
            </a>

            <x-primary-button class="ms-4">
                ถัดไป: ส่งเอกสาร
            </x-primary-button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const accountNoInput = document.getElementById('account_no');
            const communitySelect = document.getElementById('community_id');
            const houseNoInput = document.getElementById('house_no');
            const contactPersonInput = document.getElementById('contact_person');
            const passwordInput = document.getElementById('password');
            const toggleRegisterPasswordVisibilityBtn = document.getElementById('toggleRegisterPasswordVisibility');
            const addHouseholdMemberBtn = document.getElementById('addHouseholdMemberBtn');
            const householdMembersContainer = document.getElementById('householdMembersContainer');
            const householdMembersEmpty = document.getElementById('householdMembersEmpty');
            const currentYear = @json(now()->format('Y'));
            const oldMembers = @json($oldMembers);
            let memberIndex = 0;

            if (passwordInput && toggleRegisterPasswordVisibilityBtn) {
                toggleRegisterPasswordVisibilityBtn.addEventListener('click', function () {
                    const isHidden = passwordInput.type === 'password';

                    passwordInput.type = isHidden ? 'text' : 'password';
                    toggleRegisterPasswordVisibilityBtn.textContent = isHidden ? 'ซ่อน' : 'แสดง';
                    toggleRegisterPasswordVisibilityBtn.setAttribute('aria-label', isHidden ? 'ซ่อนรหัสผ่าน' : 'แสดงรหัสผ่าน');
                    toggleRegisterPasswordVisibilityBtn.setAttribute('aria-pressed', isHidden ? 'true' : 'false');
                });
            }

            function generateAccountNo() {
                const communityId = communitySelect.value.trim();
                const houseDigits = houseNoInput.value.replace(/\D/g, '');

                if (!communityId || !houseDigits) {
                    return '';
                }

                const houseSuffix = houseDigits.slice(-4).padStart(4, '0');
                return `${currentYear}${communityId}${houseSuffix}`;
            }

            function updateAccountNo() {
                accountNoInput.value = generateAccountNo();
            }

            communitySelect.addEventListener('change', updateAccountNo);
            houseNoInput.addEventListener('input', updateAccountNo);

            function contactPersonName() {
                return contactPersonInput.value.trim();
            }

            function escapeHtml(value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#39;');
            }

            function toggleMembersEmptyState() {
                householdMembersEmpty.classList.toggle('hidden', householdMembersContainer.children.length > 0);
            }

            function syncMemberRowState() {
                const memberRows = householdMembersContainer.querySelectorAll('.household-member-row');

                memberRows.forEach(function (row, index) {
                    const orderLabel = row.querySelector('.member-order-label');
                    const removeButton = row.querySelector('.remove-household-member');

                    if (orderLabel) {
                        orderLabel.textContent = `สมาชิกคนที่ ${index + 1}`;
                    }

                    if (removeButton) {
                        removeButton.disabled = memberRows.length === 1;
                    }
                });

                if (memberRows.length === 0) {
                    memberIndex = 0;
                }

                toggleMembersEmptyState();
            }

            function syncFirstMemberNameFromContact() {
                const firstRow = householdMembersContainer.querySelector('.household-member-row');

                if (!firstRow || firstRow.dataset.canAutoContactSync !== 'true' || firstRow.dataset.autoContactSync !== 'true') {
                    return;
                }

                const fullNameInput = firstRow.querySelector('[data-member-field="full_name"]');

                if (fullNameInput) {
                    fullNameInput.value = contactPersonName();
                }
            }

            function syncHeadSelection(activeCheckbox) {
                householdMembersContainer.querySelectorAll('[data-member-field="is_head"]').forEach(function (checkbox) {
                    const row = checkbox.closest('.household-member-row');
                    const relationInput = row ? row.querySelector('[data-member-field="relation"]') : null;

                    if (checkbox === activeCheckbox) {
                        if (checkbox.checked && relationInput) {
                            relationInput.value = 'หัวหน้าครัวเรือน';
                        } else if (relationInput && relationInput.value.trim() === 'หัวหน้าครัวเรือน') {
                            relationInput.value = '';
                        }

                        return;
                    }

                    checkbox.checked = false;

                    if (relationInput && relationInput.value.trim() === 'หัวหน้าครัวเรือน') {
                        relationInput.value = '';
                    }
                });
            }

            function addMemberRow(member = {}, options = {}) {
                const index = memberIndex++;
                const isHead = member.is_head === true || member.is_head === 1 || member.is_head === '1';
                const canAutoContactSync = options.autoSyncContactName === true;
                const incomingFullName = String(member.full_name ?? '').trim();
                const initialFullName = incomingFullName !== '' ? incomingFullName : (canAutoContactSync ? contactPersonName() : '');
                const initialRelation = String(member.relation ?? '').trim() || (isHead ? 'หัวหน้าครัวเรือน' : '');
                const wrapper = document.createElement('div');
                wrapper.className = 'household-member-row rounded-2xl border border-emerald-200 bg-white p-4 shadow-sm';
                wrapper.dataset.canAutoContactSync = canAutoContactSync ? 'true' : 'false';
                wrapper.dataset.autoContactSync = canAutoContactSync && (incomingFullName === '' || incomingFullName === contactPersonName()) ? 'true' : 'false';
                wrapper.innerHTML = `
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="member-order-label text-sm font-semibold text-emerald-900"></div>
                            <div class="mt-1 text-xs text-emerald-700/70">กรอกข้อมูลสมาชิกถ้าต้องการบันทึกไปพร้อมกัน</div>
                        </div>
                        <button type="button" class="remove-household-member inline-flex items-center justify-center rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-50">
                            ลบ
                        </button>
                    </div>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">ชื่อ-นามสกุล</label>
                            <input type="text" name="members[${index}][full_name]" value="${escapeHtml(initialFullName)}" data-member-field="full_name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">เลขบัตรประชาชน</label>
                            <input type="text" name="members[${index}][id_card]" value="${escapeHtml(member.id_card)}" inputmode="numeric" maxlength="13" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">ความสัมพันธ์กับหัวหน้า</label>
                            <input type="text" name="members[${index}][relation]" value="${escapeHtml(initialRelation)}" data-member-field="relation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        </div>
                    </div>

                    <label class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-emerald-900">
                        <input type="checkbox" name="members[${index}][is_head]" value="1" ${isHead ? 'checked' : ''} data-member-field="is_head" class="rounded border-gray-300 text-emerald-600 shadow-sm focus:ring-emerald-500">
                        เป็นหัวหน้าครัวเรือน
                    </label>
                `;

                const fullNameInput = wrapper.querySelector('[data-member-field="full_name"]');
                const headCheckbox = wrapper.querySelector('[data-member-field="is_head"]');
                const removeButton = wrapper.querySelector('.remove-household-member');

                fullNameInput.addEventListener('input', function () {
                    if (wrapper.dataset.canAutoContactSync !== 'true') {
                        return;
                    }

                    wrapper.dataset.autoContactSync = fullNameInput.value.trim() === contactPersonName() ? 'true' : 'false';
                });

                headCheckbox.addEventListener('change', function () {
                    syncHeadSelection(headCheckbox);
                });

                removeButton.addEventListener('click', function () {
                    if (removeButton.disabled) {
                        return;
                    }

                    wrapper.remove();
                    syncMemberRowState();
                    syncFirstMemberNameFromContact();
                });

                householdMembersContainer.appendChild(wrapper);

                if (headCheckbox.checked) {
                    syncHeadSelection(headCheckbox);
                }

                syncMemberRowState();
                syncFirstMemberNameFromContact();
            }

            addHouseholdMemberBtn.addEventListener('click', function () {
                addMemberRow({}, { autoSyncContactName: householdMembersContainer.children.length === 0 });
            });

            contactPersonInput.addEventListener('input', syncFirstMemberNameFromContact);

            if (oldMembers.length > 0) {
                oldMembers.forEach(function (member, index) {
                    addMemberRow(member, { autoSyncContactName: index === 0 });
                });
            } else {
                addMemberRow({}, { autoSyncContactName: true });
            }

            syncMemberRowState();
            updateAccountNo();
        });
    </script>
</x-guest-layout>
