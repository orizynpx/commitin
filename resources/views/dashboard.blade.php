@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
    @php
        $user = auth()->user();
        $role = $user->role;
    @endphp

    @if (session('status'))
        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-blue-700">
                        {{ session('status') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if ($role === 'student')
        <!-- STUDENT DASHBOARD -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-[#1e293b] mb-1">Selamat Datang, {{ $user->name }}!</h2>
            <p class="text-gray-500 text-sm">Temukan lowongan kepanitiaan dan kembangkan pengalaman Anda di lingkungan kampus.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Cari Lowongan Kepanitiaan</h3>
                    <p class="text-gray-500 text-sm mb-4">Temukan divisi kepanitiaan yang sesuai dengan minat dan keahlian utama Anda.</p>
                </div>
                <a href="#" class="inline-flex items-center text-blue-600 font-semibold hover:text-blue-800 text-sm">
                    Mulai Eksplorasi &rarr;
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Kelola Profil & Portofolio</h3>
                    <p class="text-gray-500 text-sm mb-4">Lengkapi data diri, keahlian, dan riwayat pengalaman untuk meningkatkan peluang diterima.</p>
                </div>
                <a href="{{ route('profile') }}" class="inline-flex items-center text-blue-600 font-semibold hover:text-blue-800 text-sm">
                    Sunting Profil &rarr;
                </a>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
            <div class="max-w-md mx-auto">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                <h4 class="text-lg font-bold text-gray-900 mb-1">Belum Ada Lamaran Aktif</h4>
                <p class="text-gray-500 text-sm mb-6">Anda belum mendaftar ke lowongan kepanitiaan mana pun saat ini.</p>
                <a href="#" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm inline-block">
                    Cari Lowongan Sekarang
                </a>
            </div>
        </div>

    @elseif ($role === 'organization')
        @php
            $profile = $user->organizationProfile;
            $status = $profile->verification_status ?? 'pending';
        @endphp

        @if ($status === 'pending')
            <!-- PENDING VERIFICATION STATE -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center my-12">
                <div class="max-w-xl mx-auto">
                    <div class="w-16 h-16 bg-amber-50 text-amber-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Pendaftaran Akun Sedang Ditinjau</h2>
                    <p class="text-gray-600 text-sm leading-relaxed mb-6">
                        Terima kasih telah mendaftar sebagai organisasi di CommitIn. Akun organisasi <strong>{{ $user->name }}</strong> saat ini sedang ditinjau oleh administrator sistem untuk proses verifikasi.
                    </p>
                    <div class="bg-amber-50 rounded-lg p-4 mb-8 text-left border border-amber-100">
                        <p class="text-xs text-amber-800 font-medium leading-relaxed">
                            💡 Selama proses peninjauan ini, Anda dapat melengkapi deskripsi profil organisasi Anda, namun Anda belum diperkenankan menerbitkan kegiatan/event atau membuka lowongan kepanitiaan.
                        </p>
                    </div>
                    <div class="flex justify-center gap-3">
                        <a href="{{ route('profile') }}" class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm">
                            Lihat Profil Organisasi
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        @elseif ($status === 'rejected')
            <!-- REJECTED VERIFICATION STATE -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center my-12">
                <div class="max-w-xl mx-auto">
                    <div class="w-16 h-16 bg-red-50 text-red-600 rounded-full flex items-center justify-center mx-auto mb-6">
                        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">Verifikasi Akun Ditolak</h2>
                    <p class="text-gray-600 text-sm leading-relaxed mb-6">
                        Mohon maaf, permohonan verifikasi akun organisasi <strong>{{ $user->name }}</strong> ditolak oleh administrator sistem karena informasi profil atau kelengkapan yang belum memenuhi syarat.
                    </p>
                    <div class="bg-red-50 rounded-lg p-4 mb-8 text-left border border-red-100">
                        <p class="text-xs text-red-800 font-medium leading-relaxed">
                            ⚠️ Silakan perbarui deskripsi organisasi, tingkat organisasi, atau informasi lainnya di halaman profil Anda. Setelah Anda memperbarui profil, permohonan Anda akan otomatis dikirimkan kembali untuk ditinjau ulang oleh admin.
                        </p>
                    </div>
                    <div class="flex justify-center gap-3">
                        <a href="{{ route('profile') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm shadow-blue-200">
                            Perbarui & Ajukan Ulang Profil
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        @else
            <!-- VERIFIED ORGANIZER DASHBOARD -->
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-[#1e293b] mb-1">Overview Rekrutmen</h2>
                <p class="text-gray-500 text-sm">Pantau proses seleksi pendaftar dan status lowongan divisi kepanitiaan Anda.</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <!-- Card 1 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">0</h3>
                    <p class="text-gray-500 text-sm">Lowongan Aktif</p>
                </div>

                <!-- Card 2 -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-teal-50 rounded-lg text-teal-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">0</h3>
                    <p class="text-gray-500 text-sm">Total Pelamar</p>
                </div>

                <!-- Card 3 -->
                <div class="bg-blue-50 rounded-xl shadow-sm border border-blue-100 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-blue-600 rounded-lg text-white shadow-md shadow-blue-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-1">0</h3>
                    <p class="text-gray-600 text-sm">Interview Dijadwalkan</p>
                </div>
            </div>

            <!-- Active Vacancies Section -->
            <div>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Daftar Lowongan Aktif</h3>
                    <a href="#" class="bg-[#0f172a] text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-black transition-colors flex items-center gap-2 shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Buat Lowongan Baru
                    </a>
                </div>
                
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
                    <div class="max-w-md mx-auto">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0V9a2 2 0 00-2-2H6a2 2 0 00-2 2v2m0 4h12"></path></svg>
                        <h4 class="text-lg font-bold text-gray-900 mb-1">Belum Ada Lowongan</h4>
                        <p class="text-gray-500 text-sm mb-6">Organisasi Anda belum menerbitkan lowongan kepanitiaan aktif saat ini.</p>
                        <a href="#" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-sm inline-block">
                            Buat Lowongan Sekarang
                        </a>
                    </div>
                </div>
            </div>
        @endif

    @elseif ($role === 'admin')
        <!-- ADMIN DASHBOARD -->
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-[#1e293b] mb-1">Dashboard Administrator</h2>
            <p class="text-gray-500 text-sm">Kelola verifikasi akun ormawa dan moderasi daftar keahlian/skill platform.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Verifikasi Ormawa</h3>
                    <p class="text-gray-500 text-sm mb-4">Tinjau dan proses pengajuan verifikasi akun dari organisasi kemahasiswaan baru.</p>
                </div>
                <a href="#" class="inline-flex items-center text-blue-600 font-semibold hover:text-blue-800 text-sm">
                    Kelola Verifikasi &rarr;
                </a>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Moderasi Keahlian</h3>
                    <p class="text-gray-500 text-sm mb-4">Kelola, setujui, ubah nama, atau gabungkan keahlian/skill tag dari mahasiswa.</p>
                </div>
                <a href="{{ route('admin.skills') }}" class="inline-flex items-center text-blue-600 font-semibold hover:text-blue-800 text-sm">
                    Kelola Keahlian &rarr;
                </a>
            </div>
        </div>
    @endif
@endsection
