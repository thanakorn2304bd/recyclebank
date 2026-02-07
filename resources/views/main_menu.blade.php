<x-app-layout>
    <!-- Hero Section with Gradient Background -->
    <div class="bg-gradient-to-br from-emerald-50 via-green-50 to-teal-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
            
            <!-- Header Section -->
            <div class="text-center mb-10 sm:mb-12">
                <div class="inline-flex items-center justify-center w-28 h-28 rounded-full bg-white shadow-lg mb-4">
                    <img src="{{ asset('images/recycle-logo.png') }}" alt="โลโก้กองทุนธนาคารวัสดุรีไซเคิ้ล" class="w-24 h-24 object-contain">
                </div>
                <h1 class="text-3xl sm:text-4xl font-bold text-emerald-900 mb-2">
                    กองทุนธนาคารวัสดุรีไซเคิ้ล
                </h1>
                <p class="text-base sm:text-lg text-emerald-700/80">
                    เทศบาลตำบลหนองไผ่
                </p>
            </div>

            <!-- Main Menu Grid -->
            <div class="grid gap-5 sm:gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-8">
                
                <!-- Card 1: รับซื้อวัสดุรีไซเคิ้ล -->
                <a href="{{ route('deposits.create') }}" 
                   class="group relative overflow-hidden rounded-3xl bg-white p-6 sm:p-8 shadow-md hover:shadow-2xl transition-all duration-300 border-2 border-transparent hover:border-emerald-300 hover:-translate-y-1">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-emerald-100 to-green-100 rounded-full -mr-16 -mt-16 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                    
                    <div class="relative">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-emerald-900 mb-2">รับซื้อวัสดุรีไซเคิ้ล</h3>
                        <p class="text-sm text-emerald-700/70 mb-4">
                            บันทึกน้ำหนักและยอดเงินฝาก
                        </p>
                        
                        <div class="flex items-center text-emerald-600 font-semibold text-sm group-hover:text-emerald-700">
                            เริ่มทำรายการ
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Card 2: จ่ายถอนวัสดุ -->
                <a href="{{ route('withdraws.create') }}" 
                   class="group relative overflow-hidden rounded-3xl bg-white p-6 sm:p-8 shadow-md hover:shadow-2xl transition-all duration-300 border-2 border-transparent hover:border-amber-300 hover:-translate-y-1">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-amber-100 to-yellow-100 rounded-full -mr-16 -mt-16 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                    
                    <div class="relative">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500 to-yellow-600 shadow-lg mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/>
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-emerald-900 mb-2">ถอน</h3>
                        <p class="text-sm text-emerald-700/70 mb-4">
                            บันทึกการถอนเงินจากบัญชี
                        </p>
                        
                        <div class="flex items-center text-amber-600 font-semibold text-sm group-hover:text-amber-700">
                            บันทึกการถอน
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Card 3: ประวัติรายการ -->
                <a href="{{ route('transactions.index') }}" 
                   class="group relative overflow-hidden rounded-3xl bg-white p-6 sm:p-8 shadow-md hover:shadow-2xl transition-all duration-300 border-2 border-transparent hover:border-blue-300 hover:-translate-y-1">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full -mr-16 -mt-16 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                    
                    <div class="relative">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-emerald-900 mb-2">ประวัติรายการ</h3>
                        <p class="text-sm text-emerald-700/70 mb-4">
                            ดูรายการฝาก-ถอนย้อนหลัง
                        </p>
                        
                        <div class="flex items-center text-blue-600 font-semibold text-sm group-hover:text-blue-700">
                            เรียกดูข้อมูล
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Card 4: รายการวัสดุ -->
                <a href="{{ route('materials.index') }}" 
                   class="group relative overflow-hidden rounded-3xl bg-white p-6 sm:p-8 shadow-md hover:shadow-2xl transition-all duration-300 border-2 border-transparent hover:border-teal-300 hover:-translate-y-1">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-teal-100 to-cyan-100 rounded-full -mr-16 -mt-16 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                    
                    <div class="relative">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-teal-500 to-cyan-600 shadow-lg mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-emerald-900 mb-2">รายการวัสดุ</h3>
                        <p class="text-sm text-emerald-700/70 mb-4">
                            จัดการประเภทวัสดุรีไซเคิล
                        </p>
                        
                        <div class="flex items-center text-teal-600 font-semibold text-sm group-hover:text-teal-700">
                            เพิ่ม/แก้ไขวัสดุ
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Card 5: หมวดหมู่วัสดุ -->
                <a href="{{ route('material-categories.index') }}" 
                   class="group relative overflow-hidden rounded-3xl bg-white p-6 sm:p-8 shadow-md hover:shadow-2xl transition-all duration-300 border-2 border-transparent hover:border-purple-300 hover:-translate-y-1">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-100 to-pink-100 rounded-full -mr-16 -mt-16 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                    
                    <div class="relative">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-pink-600 shadow-lg mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-emerald-900 mb-2">หมวดหมู่วัสดุ</h3>
                        <p class="text-sm text-emerald-700/70 mb-4">
                            จัดการกลุ่มหมวดหมู่วัสดุ
                        </p>
                        
                        <div class="flex items-center text-purple-600 font-semibold text-sm group-hover:text-purple-700">
                            จัดการหมวดหมู่
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>

                <!-- Card 6: ราคาวัสดุ -->
                <a href="{{ route('material-prices.index') }}" 
                   class="group relative overflow-hidden rounded-3xl bg-white p-6 sm:p-8 shadow-md hover:shadow-2xl transition-all duration-300 border-2 border-transparent hover:border-green-300 hover:-translate-y-1">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-green-100 to-lime-100 rounded-full -mr-16 -mt-16 opacity-50 group-hover:opacity-100 transition-opacity"></div>
                    
                    <div class="relative">
                        <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-gradient-to-br from-green-500 to-lime-600 shadow-lg mb-4 group-hover:scale-110 transition-transform">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-emerald-900 mb-2">ราคาวัสดุ</h3>
                        <p class="text-sm text-emerald-700/70 mb-4">
                            ตั้งค่าราคาวัสดุปัจจุบัน
                        </p>
                        
                        <div class="flex items-center text-green-600 font-semibold text-sm group-hover:text-green-700">
                            จัดการราคา
                            <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    </div>
                </a>

            </div>

            <!-- Footer Info -->
            <div class="text-center">
                <div class="inline-flex items-center px-6 py-3 rounded-full bg-white/80 shadow-sm border border-emerald-200/50">
                    <svg class="w-4 h-4 text-emerald-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm text-emerald-700">
                        อัปเดตล่าสุด {{ now()->format('d/m/Y H:i') }} น.
                    </span>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
