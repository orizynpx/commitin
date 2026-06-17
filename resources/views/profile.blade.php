    @extends('layouts.app')
    @section('title', 'Profil Mahasiswa')

    @section('content')
    <!-- Header / Hero Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <!-- Cover Image -->
        <div class="h-48 bg-gradient-to-r from-blue-600 to-blue-400 w-full relative">
            <div class="absolute inset-0 bg-black/10"></div>
        </div>
        
        <!-- Profile Info -->
        <div class="px-8 pb-8 relative">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-end -mt-16 mb-4 gap-4">
                <!-- Avatar & Name -->
                <div class="flex flex-col md:flex-row items-start md:items-end gap-6">
                    <div class="w-32 h-32 rounded-full border-4 border-white bg-white overflow-hidden shadow-md shrink-0 relative z-10">
                        <img src="https://ui-avatars.com/api/?name=Budi+Mahasiswa&background=f8fafc&color=2563eb&size=256" alt="Profile avatar" class="w-full h-full object-cover">
                    </div>
                    <div class="mb-2">
                        <h1 class="text-3xl font-bold text-gray-900 mb-1">Budi Mahasiswa</h1>
                        <p class="text-lg text-blue-600 font-medium mb-1">Sistem Informasi &bull; Angkatan 2024</p>
                        <div class="flex items-center text-sm text-gray-500 gap-4">
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                Fakultas Ilmu Komputer
                            </span>
                            <span class="flex items-center gap-1 text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full font-medium">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                                Terverifikasi
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="flex gap-3 w-full md:w-auto">
                    <button class="flex-1 md:flex-none bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors flex items-center justify-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Unduh CV
                    </button>
                    <button class="flex-1 md:flex-none bg-blue-600 text-white hover:bg-blue-700 px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm shadow-blue-200 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit Profil
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        
        <!-- Left/Main Column -->
        <div class="lg:col-span-2 space-y-8">
            
            <!-- About Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Tentang Saya
                </h3>
                <p class="text-gray-600 leading-relaxed text-sm">
                    Mahasiswa tingkat 3 Sistem Informasi yang memiliki minat besar dalam pengembangan antarmuka (UI/UX) dan manajemen acara (Event Management). 
                    Saya aktif dalam berbagai kegiatan kampus terutama yang berkaitan dengan teknologi dan kreativitas. 
                    Memiliki kemampuan komunikasi yang baik dan mampu bekerja dalam tim maupun secara mandiri.
                </p>
            </div>

            <!-- Experience Timeline -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        Pengalaman Kepanitiaan & Organisasi
                    </h3>
                    <button class="text-sm font-medium text-blue-600 hover:text-blue-800">+ Tambah</button>
                </div>
                
                <div class="relative border-l-2 border-blue-100 ml-3 space-y-8">
                    <!-- Item 1 -->
                    <div class="relative pl-6">
                        <div class="absolute w-4 h-4 bg-blue-600 rounded-full -left-[9px] top-1 border-4 border-white shadow-sm"></div>
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="text-lg font-bold text-gray-900">Koordinator Divisi Publikasi & Dekorasi</h4>
                            <span class="text-xs font-medium bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full">2025</span>
                        </div>
                        <p class="text-sm font-medium text-blue-600 mb-2">Pekan IT Nasional (PINTAR) Universitas</p>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Memimpin tim beranggotakan 8 orang untuk merancang aset visual, kampanye media sosial, dan dekorasi panggung utama. 
                            Berhasil meningkatkan *engagement* media sosial sebesar 150% dibandingkan tahun sebelumnya.
                        </p>
                    </div>
                    
                    <!-- Item 2 -->
                    <div class="relative pl-6">
                        <div class="absolute w-4 h-4 bg-gray-300 rounded-full -left-[9px] top-1 border-4 border-white"></div>
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="text-lg font-bold text-gray-900">Staff UI/UX Designer</h4>
                            <span class="text-xs font-medium bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">2024 - 2025</span>
                        </div>
                        <p class="text-sm font-medium text-gray-700 mb-2">BEM Fakultas Ilmu Komputer</p>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Mendesain prototipe aplikasi untuk program kerja internal BEM. Berkolaborasi dengan tim *developer* untuk memastikan desain dapat diimplementasikan dengan baik menggunakan Figma dan TailwindCSS.
                        </p>
                    </div>
                    
                    <!-- Item 3 -->
                    <div class="relative pl-6">
                        <div class="absolute w-4 h-4 bg-gray-300 rounded-full -left-[9px] top-1 border-4 border-white"></div>
                        <div class="flex justify-between items-start mb-1">
                            <h4 class="text-lg font-bold text-gray-900">Anggota Divisi Acara</h4>
                            <span class="text-xs font-medium bg-gray-100 text-gray-600 px-2.5 py-1 rounded-full">2024</span>
                        </div>
                        <p class="text-sm font-medium text-gray-700 mb-2">Ospek Jurusan Sistem Informasi</p>
                        <p class="text-sm text-gray-600 leading-relaxed">
                            Membantu menyusun *rundown* acara dan bertindak sebagai pendamping bagi 30 mahasiswa baru selama masa orientasi.
                        </p>
                    </div>
                </div>
            </div>

        </div>
        
        <!-- Right Sidebar Column -->
        <div class="space-y-8">
            
            <!-- Skills Section -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Keahlian Utama
                    </h3>
                    <button class="text-gray-400 hover:text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg></button>
                </div>
                
                <div class="mb-4">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Hard Skills</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-blue-50 border border-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">UI/UX Design</span>
                        <span class="bg-blue-50 border border-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">Figma</span>
                        <span class="bg-blue-50 border border-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">HTML/CSS</span>
                        <span class="bg-blue-50 border border-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">TailwindCSS</span>
                        <span class="bg-blue-50 border border-blue-100 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">Adobe Illustrator</span>
                    </div>
                </div>
                
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Soft Skills</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="bg-gray-100 border border-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">Leadership</span>
                        <span class="bg-gray-100 border border-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">Public Speaking</span>
                        <span class="bg-gray-100 border border-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">Event Management</span>
                        <span class="bg-gray-100 border border-gray-200 text-gray-700 px-3 py-1 rounded-full text-sm font-medium">Teamwork</span>
                    </div>
                </div>
            </div>
            
            <!-- Portfolio Attachments -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path></svg>
                        Portofolio & Berkas
                    </h3>
                    <button class="text-blue-600 hover:text-blue-800 font-medium text-sm">+ Upload</button>
                </div>
                
                <div class="space-y-3">
                    <!-- File 1 -->
                    <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors group cursor-pointer">
                        <div class="w-10 h-10 rounded bg-red-100 text-red-500 flex items-center justify-center mr-3 shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-700">CV_Budi_Mahasiswa_2026.pdf</p>
                            <p class="text-xs text-gray-500">1.2 MB &bull; Diunggah kemarin</p>
                        </div>
                    </div>
                    
                    <!-- File 2 -->
                    <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors group cursor-pointer">
                        <div class="w-10 h-10 rounded bg-blue-100 text-blue-500 flex items-center justify-center mr-3 shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 3a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V5a2 2 0 00-2-2H4zm12 12H4l4-8 3 6 2-4 3 6z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-700">Portofolio_UIUX_Design.jpg</p>
                            <p class="text-xs text-gray-500">3.5 MB &bull; Diunggah 12 Okt</p>
                        </div>
                    </div>
                    
                    <!-- File 3 -->
                    <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors group cursor-pointer">
                        <div class="w-10 h-10 rounded bg-amber-100 text-amber-500 flex items-center justify-center mr-3 shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4zm2 6a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1zm1 3a1 1 0 100 2h6a1 1 0 100-2H7z" clip-rule="evenodd"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-gray-900 truncate group-hover:text-blue-700">Sertifikat_Lomba_IT.pdf</p>
                            <p class="text-xs text-gray-500">800 KB &bull; Diunggah 05 Sep</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
    @endsection
