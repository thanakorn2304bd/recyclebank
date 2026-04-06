<div
    x-data='{
        activeCategory: "all",
        categoryFilters: @json($categoryFilters),
        totalMaterials: {{ $totalMaterials }},
        get activeCategoryLabel() {
            if (this.activeCategory === "all") {
                return "ทั้งหมด";
            }

            const selected = this.categoryFilters.find((filter) => filter.name === this.activeCategory);

            return selected ? selected.name : "ทั้งหมด";
        },
        get visibleCount() {
            if (this.activeCategory === "all") {
                return this.totalMaterials;
            }

            const selected = this.categoryFilters.find((filter) => filter.name === this.activeCategory);

            return selected ? selected.count : this.totalMaterials;
        }
    }'
    class="overflow-hidden rounded-3xl border border-emerald-100 bg-white/90 shadow-sm"
>
    <div class="border-b border-emerald-100 p-4 sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <div class="inline-flex items-center gap-2 rounded-full border border-emerald-100 bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                    {{ $authUser ? 'ข้อมูลราคาสำหรับใช้งานประจำวัน' : 'เปิดให้ดูราคาโดยไม่ต้องเข้าสู่ระบบ' }}
                </div>
                <h2 class="mt-3 text-2xl font-bold text-emerald-900 sm:text-3xl">รายการวัสดุและราคา</h2>
                <p class="mt-1 text-sm text-emerald-700/70">แสดงราคาปัจจุบันที่มีผลใช้งาน</p>
            </div>

            @if(!$authUser)
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                        ลงชื่อเข้าใช้
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-white px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:border-emerald-300 hover:text-emerald-800">
                        สมัครสมาชิก
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="p-4 sm:p-6">
        @if($totalCategories > 1)
            <div class="mb-6 rounded-3xl border border-emerald-100 bg-[linear-gradient(135deg,rgba(236,253,245,0.96),rgba(255,255,255,0.98))] p-4 shadow-[inset_0_1px_0_rgba(255,255,255,0.7)]">
                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <div class="text-sm font-semibold text-emerald-900">กรองตามหมวดหมู่</div>
                        <p class="mt-1 text-xs text-emerald-700/80 sm:text-sm">
                            เลือกดูเฉพาะหมวดที่สนใจเพื่อไล่เช็กราคาได้ง่ายขึ้น
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 text-xs font-medium text-emerald-700/80">
                        <span class="rounded-full border border-emerald-200 bg-white/80 px-3 py-1">
                            {{ $totalCategories }} หมวด
                        </span>
                        <span class="rounded-full border border-emerald-200 bg-white/80 px-3 py-1">
                            <span x-text="visibleCount"></span> รายการที่แสดง
                        </span>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap items-start gap-2">
                    <button
                        type="button"
                        @click='activeCategory = "all"'
                        :aria-pressed='activeCategory === "all"'
                        :class='activeCategory === "all"
                            ? "border-emerald-600 bg-emerald-600 text-white shadow-sm shadow-emerald-200"
                            : "border-white/80 bg-white/90 text-emerald-800 hover:border-emerald-200 hover:bg-white"'
                        class="inline-flex max-w-full flex-wrap items-start gap-2 rounded-full border px-3 py-2 text-left text-sm font-semibold leading-5 transition sm:flex-nowrap sm:items-center"
                    >
                        <span
                            :class='activeCategory === "all" ? "bg-white" : "bg-emerald-500"'
                            class="mt-1 h-2 w-2 shrink-0 rounded-full sm:mt-0"
                        ></span>
                        <span class="min-w-0 break-words">ทั้งหมด</span>
                        <span
                            :class='activeCategory === "all" ? "bg-white/15 text-white" : "bg-emerald-50 text-emerald-700"'
                            class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold"
                        >
                            {{ $totalMaterials }}
                        </span>
                    </button>

                    @foreach($categoryFilters as $filter)
                        <button
                            type="button"
                            @click='activeCategory = @json($filter["name"])'
                            :aria-pressed='activeCategory === @json($filter["name"])'
                            :class='activeCategory === @json($filter["name"])
                                ? "border-emerald-600 bg-emerald-600 text-white shadow-sm shadow-emerald-200"
                                : "border-white/80 bg-white/90 text-emerald-800 hover:border-emerald-200 hover:bg-white"'
                            class="inline-flex max-w-full flex-wrap items-start gap-2 rounded-full border px-3 py-2 text-left text-sm font-semibold leading-5 transition sm:flex-nowrap sm:items-center"
                        >
                            <span
                                :class='activeCategory === @json($filter["name"]) ? "bg-white" : "bg-emerald-500"'
                                class="mt-1 h-2 w-2 shrink-0 rounded-full sm:mt-0"
                            ></span>
                            <span class="min-w-0 break-words">{{ $filter['name'] }}</span>
                            <span
                                :class='activeCategory === @json($filter["name"]) ? "bg-white/15 text-white" : "bg-emerald-50 text-emerald-700"'
                                class="shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold"
                            >
                                {{ $filter['count'] }}
                            </span>
                        </button>
                    @endforeach
                </div>

                <div class="mt-3 text-xs text-emerald-700/80">
                    กำลังแสดง:
                    <span class="font-semibold text-emerald-900" x-text="activeCategoryLabel"></span>
                </div>
            </div>
        @endif

        @forelse($catalogSections as $section)
            <div
                x-show='activeCategory === "all" || activeCategory === @json($section["name"])'
                x-transition.opacity.duration.200ms
                class="mb-6 last:mb-0"
            >
                <div class="flex items-center gap-2">
                    <span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
                    <h3 class="text-lg font-semibold break-words text-emerald-900">{{ $section['name'] }}</h3>
                    <span class="rounded-full border border-emerald-100 bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">
                        {{ $section['count'] }} รายการ
                    </span>
                </div>

                <div class="mt-3 overflow-hidden rounded-2xl border border-emerald-200 bg-emerald-100 p-px shadow-sm">
                    <div class="grid gap-px bg-emerald-100 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($section['items'] as $item)
                            <div class="bg-white p-4">
                                <div class="text-base font-semibold break-words text-emerald-900">{{ $item['name'] }}</div>
                                <div class="mt-1 text-sm text-emerald-700">
                                    ราคา/หน่วย:
                                    @if($item['priceDisplay'] !== null)
                                        <span class="font-semibold text-emerald-900">{{ $item['priceDisplay'] }}</span>
                                        <span class="text-emerald-700">บาท/{{ $item['unit'] }}</span>
                                    @else
                                        <span class="text-amber-600">ยังไม่มีราคา</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-emerald-100 bg-white p-6 text-center text-emerald-700/70">
                ยังไม่มีรายการวัสดุ
            </div>
        @endforelse
    </div>
</div>
