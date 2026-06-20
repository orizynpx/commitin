<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\VacancyApplication;

new #[Layout('layouts.app')] class extends Component
{
    public VacancyApplication $application;
    
    // Evaluation form fields
    public string $status = '';
    public string $interview_scheduled_at = '';
    public string $interview_format = 'online';
    public string $interview_location = '';
    public string $feedback = '';

    public function mount(string $application): void
    {
        $this->application = VacancyApplication::whereHas('vacancy.event.organizers', function($q) {
            $q->where('event_organizers.user_id', auth()->id());
        })->with(['vacancy.event', 'user.studentProfile', 'user.skills', 'user.experiences'])->findOrFail($application);

        $this->status = $this->application->status;
        $this->interview_scheduled_at = $this->application->interview_scheduled_at ? $this->application->interview_scheduled_at->format('Y-m-d\TH:i') : '';
        $this->interview_format = $this->application->interview_format ?? 'online';
        $this->interview_location = $this->application->interview_location ?? '';
        $this->feedback = $this->application->feedback ?? '';
    }

    public function saveEvaluation()
    {
        $rules = [
            'status' => 'required|in:pending,interviewing,accepted,rejected',
            'feedback' => 'nullable|string',
        ];

        if ($this->status === 'interviewing') {
            $rules['interview_scheduled_at'] = 'required';
            $rules['interview_format'] = 'required|in:online,offline';
            $rules['interview_location'] = 'required|string|max:2083';
        }

        $this->validate($rules);

        $updateData = [
            'status' => $this->status,
            'feedback' => $this->feedback,
        ];

        if ($this->status === 'interviewing') {
            $updateData['interview_scheduled_at'] = $this->interview_scheduled_at;
            $updateData['interview_format'] = $this->interview_format;
            $updateData['interview_location'] = $this->interview_location;
        } else {
            $updateData['interview_scheduled_at'] = null;
            $updateData['interview_format'] = null;
            $updateData['interview_location'] = null;
        }

        $this->application->update($updateData);

        session()->flash('success', 'Evaluasi pelamar berhasil disimpan.');
    }
}; ?>

<div class="max-w-5xl mx-auto py-8">
    <div class="mb-6">
        <a href="{{ route('organizer.applications.index') }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold flex items-center gap-1">
            &larr; Kembali ke Daftar Pelamar
        </a>
    </div>

    @if(session()->has('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl p-4 mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Applicant Profile (Left Col) -->
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1 block">
                    Pelamar Divisi {{ $application->vacancy->division }} - {{ $application->vacancy->event->event_name }}
                </span>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $application->user->name }}</h1>
                <p class="text-sm text-gray-500 mb-6">{{ $application->user->email }}</p>

                <!-- Academic Info -->
                @if($application->user->studentProfile)
                    <div class="grid grid-cols-2 gap-4 pb-6 mb-6 border-b border-gray-100 text-sm">
                        <div>
                            <span class="text-xs text-gray-400 block font-semibold uppercase">NIM / ID Mahasiswa</span>
                            <strong>{{ $application->user->studentProfile->student_id }}</strong>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block font-semibold uppercase">Program Studi / Fakultas</span>
                            <strong>{{ $application->user->studentProfile->study_program }} ({{ $application->user->studentProfile->faculty }})</strong>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block font-semibold uppercase">Angkatan</span>
                            <strong>{{ $application->user->studentProfile->entry_year }}</strong>
                        </div>
                        <div>
                            <span class="text-xs text-gray-400 block font-semibold uppercase">Bio Singkat</span>
                            <span class="text-gray-600 italic">{{ $application->user->studentProfile->bio ?? '-' }}</span>
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-gray-50 rounded-lg text-xs text-gray-500 mb-6">
                        Mahasiswa belum melengkapi profil akademik mereka.
                    </div>
                @endif

                <!-- Candidate Skills -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase mb-3">Keahlian Mahasiswa</h3>
                    <div class="flex flex-wrap gap-2">
                        @forelse($application->user->skills as $sk)
                            <span class="text-xs bg-blue-50 text-blue-700 border border-blue-100 px-2.5 py-1 rounded-md font-medium">
                                {{ $sk->skill_name }}
                            </span>
                        @empty
                            <span class="text-xs text-gray-400 italic">Belum mencantumkan keahlian khusus.</span>
                        @endforelse
                    </div>
                </div>

                <!-- Experiences -->
                <div>
                    <h3 class="text-sm font-semibold text-gray-400 uppercase mb-3">Riwayat Pengalaman</h3>
                    <div class="space-y-3">
                        @forelse($application->user->experiences as $exp)
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-100 text-xs">
                                <div class="font-bold text-gray-900 mb-0.5">{{ $exp->title }}</div>
                                <div class="text-gray-400 mb-2">Diperbarui {{ $exp->updated_at->format('d M Y') }}</div>
                                <p class="text-gray-600 leading-normal">{{ $exp->description }}</p>
                            </div>
                        @empty
                            <span class="text-xs text-gray-400 italic">Belum ada riwayat pengalaman kepanitiaan/organisasi.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- CV / Attachment View -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-gray-900 text-sm mb-1">Tautan Lampiran CV / Berkas Pendukung</h3>
                    <p class="text-xs text-gray-500">Buka berkas di tab baru untuk meninjau kualifikasi pelamar.</p>
                </div>
                <a 
                    href="{{ $application->file_url }}" 
                    target="_blank" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs px-4 py-2.5 rounded-lg transition-colors inline-flex items-center gap-1.5"
                >
                    Buka Berkas &rarr;
                </a>
            </div>
        </div>

        <!-- Evaluation Form (Right Col) -->
        <div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Formulir Evaluasi</h3>
                <form wire:submit.prevent="saveEvaluation" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Ubah Status</label>
                        <select 
                            wire:model.live="status" 
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        >
                            <option value="pending">Ditinjau (Pending)</option>
                            <option value="interviewing">Wawancara (Interviewing)</option>
                            <option value="accepted">Diterima (Accepted)</option>
                            <option value="rejected">Ditolak (Rejected)</option>
                        </select>
                    </div>

                    @if($status === 'interviewing')
                        <div class="space-y-4 border-t border-gray-50 pt-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Tanggal & Waktu Wawancara</label>
                                <input 
                                    type="datetime-local" 
                                    wire:model="interview_scheduled_at" 
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                                @error('interview_scheduled_at')
                                    <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Format Wawancara</label>
                                <select 
                                    wire:model="interview_format" 
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="online">Online</option>
                                    <option value="offline">Offline</option>
                                </select>
                                @error('interview_format')
                                    <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Lokasi / Tautan Wawancara</label>
                                <input 
                                    type="text" 
                                    wire:model="interview_location" 
                                    placeholder="Link Zoom/Meet atau nama ruang wawancara..." 
                                    class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                                @error('interview_location')
                                    <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Catatan Evaluasi / Alasan Penolakan</label>
                        <textarea 
                            wire:model="feedback" 
                            rows="4" 
                            placeholder="Catatan dari panitia untuk mahasiswa..." 
                            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        ></textarea>
                        @error('feedback')
                            <span class="text-xs text-red-500 block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded-lg text-sm transition-colors shadow-sm"
                    >
                        Simpan Evaluasi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
