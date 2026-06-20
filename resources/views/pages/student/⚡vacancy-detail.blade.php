<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Vacancy;
use App\Models\VacancyApplication;
use Illuminate\Support\Facades\Storage;

new #[Layout('layouts.app')] class extends Component
{
    use WithFileUploads;

    public Vacancy $vacancy;
    public $file;

    public function mount(Vacancy $vacancy): void
    {
        $this->vacancy = $vacancy->loadMissing(['event', 'skills']);
    }

    public function apply()
    {
        $this->validate([
            'file' => 'required|file|mimes:pdf|max:10240',
        ]);

        // Check if already applied
        $existing = VacancyApplication::where('user_id', auth()->id())
            ->where('vacancy_id', $this->vacancy->vacancy_id)
            ->first();

        if ($existing) {
            session()->flash('error', 'Anda sudah melamar lowongan ini.');
            return;
        }

        // Store the uploaded file to the local public disk (storage/app/public/applications)
        $path = $this->file->store('applications', 'public');
        $file_url = Storage::url($path);

        VacancyApplication::create([
            'user_id' => auth()->id(),
            'vacancy_id' => $this->vacancy->vacancy_id,
            'status' => 'pending',
            'file_url' => $file_url,
        ]);

        session()->flash('success', 'Lamaran Anda berhasil dikirim!');
        $this->file = null;
    }
}; ?>

<div class="max-w-4xl mx-auto py-8">
    <div class="mb-6">
        <a href="{{ route('vacancies.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1">
            &larr; Kembali ke Eksplorasi
        </a>
    </div>

    @php
        $existingApp = App\Models\VacancyApplication::where('user_id', auth()->id())
            ->where('vacancy_id', $vacancy->vacancy_id)
            ->first();
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Details Column -->
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <span class="text-xs font-semibold uppercase tracking-wider text-blue-600 mb-1 block">
                    {{ $vacancy->event->event_name }}
                </span>
                <h1 class="text-3xl font-bold text-gray-900 mb-4">{{ $vacancy->division }}</h1>
                
                <div class="flex items-center gap-6 mb-6 text-sm text-gray-500 pb-6 border-b border-gray-100">
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase">Tanggal Event</span>
                        <strong>{{ $vacancy->event->event_date?->format('d M Y') ?? '-' }}</strong>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase">Status Lowongan</span>
                        <strong class="{{ $vacancy->status === 'OPEN' ? 'text-green-600' : 'text-red-600' }}">{{ $vacancy->status }}</strong>
                    </div>
                    <div>
                        <span class="block text-xs font-semibold text-gray-400 uppercase">Kategori</span>
                        <strong>{{ $vacancy->event->is_official ? 'Resmi Kampus' : 'Umum' }}</strong>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-gray-900 mb-3">Deskripsi Pekerjaan / Divisi</h3>
                <div class="text-gray-600 text-sm leading-relaxed whitespace-pre-line mb-6">
                    {{ $vacancy->vacancy_description }}
                </div>

                <h3 class="text-lg font-bold text-gray-900 mb-3">Keahlian Yang Dibutuhkan</h3>
                <div class="flex flex-wrap gap-2">
                    @forelse($vacancy->skills as $vsk)
                        @php
                            $hasSkill = in_array($vsk->skill_id, auth()->user()->skills->pluck('skill_id')->toArray());
                        @endphp
                        <span class="text-sm px-3 py-1.5 rounded-lg {{ $hasSkill ? 'bg-green-50 text-green-700 border border-green-200 font-medium' : 'bg-gray-50 text-gray-600 border border-gray-100' }}">
                            {{ $vsk->skill_name }} {{ $hasSkill ? '✓' : '' }}
                        </span>
                    @empty
                        <span class="text-sm text-gray-400 italic">Tidak ada keahlian khusus yang disyaratkan.</span>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Application Form Column -->
        <div>
            @if(session()->has('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 mb-6 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(session()->has('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl p-4 mb-6 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6">
                @if($existingApp)
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Status Lamaran</h3>
                    <div class="space-y-4">
                        <div class="p-3 bg-gray-50 border border-gray-100 rounded-lg text-center">
                            <span class="text-xs text-gray-400 uppercase font-semibold block mb-1">Status</span>
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold uppercase 
                                {{ $existingApp->status === 'accepted' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $existingApp->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}
                                {{ $existingApp->status === 'interviewing' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $existingApp->status === 'pending' ? 'bg-amber-100 text-amber-800' : '' }}
                            ">
                                {{ $existingApp->status }}
                            </span>
                        </div>

                        @if($existingApp->status === 'interviewing')
                            <div class="bg-blue-50/50 border border-blue-100 rounded-lg p-4 text-xs space-y-2">
                                <h4 class="font-bold text-blue-900 mb-1">Informasi Wawancara</h4>
                                <div>
                                    <span class="text-gray-500 block">Jadwal:</span>
                                    <strong>{{ $existingApp->interview_scheduled_at?->format('d M Y - H:i') ?? 'Segera dijadwalkan' }}</strong>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">Format:</span>
                                    <strong>{{ ucfirst($existingApp->interview_format ?? '-') }}</strong>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">Lokasi / Link:</span>
                                    @if(filter_var($existingApp->interview_location, FILTER_VALIDATE_URL))
                                        <a href="{{ $existingApp->interview_location }}" target="_blank" class="text-blue-600 underline font-semibold break-all">
                                            {{ $existingApp->interview_location }}
                                        </a>
                                    @else
                                        <strong>{{ $existingApp->interview_location ?? '-' }}</strong>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($existingApp->feedback)
                            <div class="bg-gray-50 rounded-lg p-4 text-xs">
                                <span class="text-gray-400 block mb-1 font-semibold">Catatan dari Panitia:</span>
                                <p class="text-gray-700 italic whitespace-pre-line">{{ $existingApp->feedback }}</p>
                            </div>
                        @endif

                        <div class="text-center pt-2">
                            <a href="{{ route('applications.download', $existingApp) }}" target="_blank" class="text-xs text-blue-600 hover:underline">
                                Lihat Dokumen Lamaran Anda &nearr;
                            </a>
                        </div>
                    </div>
                @elseif($vacancy->status !== 'OPEN')
                    <div class="text-center py-6">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m0 0v2m0-2h2m-2 0H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <h4 class="font-bold text-gray-800 mb-1">Pendaftaran Ditutup</h4>
                        <p class="text-xs text-gray-500">Lowongan kepanitiaan ini sudah ditutup dan tidak menerima lamaran baru.</p>
                    </div>
                @else
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Kirim Lamaran</h3>
                    <form wire:submit.prevent="apply" class="space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Unggah CV/Portofolio (PDF)</label>
                            <input 
                                type="file" 
                                wire:model="file" 
                                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 @error('file') border-red-500 @enderror"
                            />
                            <div wire:loading wire:target="file" class="text-xs text-blue-500 mt-1">
                                Sedang mengunggah berkas...
                            </div>
                            @error('file')
                                <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                            @enderror
                            <p class="text-[10px] text-gray-400 mt-1 leading-normal">
                                Unggah berkas resume/portfolio Anda dalam format PDF dengan ukuran maksimal 10MB.
                            </p>
                        </div>

                        <button 
                            type="submit" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors shadow-sm"
                        >
                            Kirim Lamaran Sekarang
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
