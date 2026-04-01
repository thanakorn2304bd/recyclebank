<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-emerald-900">{{ $privacyNotice->title }}</h1>
        <p class="mt-2 text-sm text-gray-600">
            เวอร์ชัน {{ $privacyNotice->version_code }} | มีผลตั้งแต่ {{ $privacyNotice->effective_at?->format('d/m/Y H:i') ?? '-' }}
        </p>
    </div>

    <div class="rounded-2xl border border-emerald-100 bg-emerald-50/70 p-4 text-sm leading-7 text-emerald-950">
        {{ $privacyNotice->summary }}
    </div>

    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="prose prose-sm max-w-none text-gray-800">
            {!! nl2br(e($privacyNotice->content)) !!}
        </div>
    </div>

    <div class="mt-6 flex justify-end">
        <a
            href="{{ route('register') }}"
            class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700"
        >
            กลับไปสมัครสมาชิก
        </a>
    </div>
</x-guest-layout>
