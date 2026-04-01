<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if(session('approval_pending_notice'))
        <div
            x-data="{ open: true }"
            x-show="open"
            class="mb-4 overflow-hidden rounded-2xl border border-amber-200 bg-amber-50 shadow-sm"
        >
            <div class="border-b border-amber-200 bg-amber-100/80 px-4 py-3">
                <div class="text-sm font-semibold text-amber-900">สถานะคำขอสมัครสมาชิก</div>
            </div>
            <div class="px-4 py-4">
                <p class="text-sm leading-6 text-amber-900">{{ session('approval_pending_notice') }}</p>
                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        @click="open = false"
                        class="inline-flex items-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-amber-700"
                    >
                        รับทราบ
                    </button>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Username -->
        <div>
            <x-input-label for="username" value="ชื่อผู้ใช้" />
            <x-text-input id="username" class="block mt-1 w-full" type="text" name="username" :value="old('username')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
            <p class="mt-2 text-sm text-gray-500">ครัวเรือนใช้เลขบัญชีเป็นชื่อผู้ใช้ในการเข้าสู่ระบบ</p>
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="รหัสผ่าน" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-emerald-700 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500" href="{{ route('register') }}">
                สมัครสมาชิกครัวเรือน
            </a>

            <x-primary-button class="ms-3">
                เข้าสู่ระบบ
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
