<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-emerald-900">สมัครสมาชิกครัวเรือน</h1>
        <p class="mt-2 text-sm text-gray-600">
            กรอกข้อมูลครัวเรือนเพื่อส่งคำขอสมัครเข้าใช้งาน ระบบจะตั้งสถานะเป็นรออนุมัติ และ staff/admin จะตรวจสอบก่อนเปิดใช้งานบัญชี
        </p>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="account_no" value="เลขบัญชี" />
            <x-text-input id="account_no" class="block mt-1 w-full bg-gray-100 text-gray-700" type="text" value="{{ old('account_no') }}" maxlength="10" inputmode="numeric" readonly />
            <x-input-error :messages="$errors->get('account_no')" class="mt-2" />
            <p class="mt-2 text-sm text-gray-500">ระบบสร้างอัตโนมัติจากปีปัจจุบัน + ชุมชน + บ้านเลขที่ และให้ staff/admin แก้ไขได้ภายหลังเท่านั้น</p>
        </div>

        <div class="mt-4">
            <x-input-label for="community_id" value="เลขที่ชุมชน" />
            <select id="community_id" name="community_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500" required autofocus>
                <option value="">-- เลือกชุมชน --</option>
                @foreach($communities as $community)
                    <option value="{{ $community->community_id }}" @selected(old('community_id') == $community->community_id)>
                        {{ $community->community_id }} - {{ $community->community_name }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('community_id')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="house_no" value="บ้านเลขที่" />
            <x-text-input id="house_no" class="block mt-1 w-full" type="text" name="house_no" :value="old('house_no')" required />
            <x-input-error :messages="$errors->get('house_no')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="village_no" value="หมู่" />
            <x-text-input id="village_no" class="block mt-1 w-full" type="text" name="village_no" :value="old('village_no')" />
            <x-input-error :messages="$errors->get('village_no')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="contact_person" value="ชื่อผู้ติดต่อ" />
            <x-text-input id="contact_person" class="block mt-1 w-full" type="text" name="contact_person" :value="old('contact_person')" required />
            <x-input-error :messages="$errors->get('contact_person')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="phone" value="เบอร์โทร" />
            <x-text-input id="phone" class="block mt-1 w-full" type="text" name="phone" :value="old('phone')" />
            <x-input-error :messages="$errors->get('phone')" class="mt-2" />
        </div>

        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
            หลังสมัคร ระบบจะสร้างบัญชีครัวเรือนในสถานะรออนุมัติ และจะยังไม่สามารถเข้าสู่ระบบได้จนกว่า staff/admin จะยืนยัน
        </div>

        <div class="mt-4">
            <x-input-label for="password" value="รหัสผ่าน" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

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
                สมัครสมาชิก
            </x-primary-button>
        </div>
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const accountNoInput = document.getElementById('account_no');
            const communitySelect = document.getElementById('community_id');
            const houseNoInput = document.getElementById('house_no');
            const currentYear = @json(now()->format('Y'));

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

            updateAccountNo();
        });
    </script>
</x-guest-layout>
