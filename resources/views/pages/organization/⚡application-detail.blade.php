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

    public function mount(VacancyApplication $application): void
    {
        $hasAccess = $application->vacancy->event->organizers()->where('users.user_id', auth()->id())->exists();
        if (!$hasAccess) {
            abort(403, 'Unauthorized.');
        }

        $this->application = $application->loadMissing(['vacancy.event', 'user.studentProfile', 'user.skills', 'user.experiences']);

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
        <a href="{{ route('organizer.applications.index') }}" class="text-primary hover:text-on-primary-container text-sm font-semibold flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg> Kembali ke Daftar Pelamar
        </a>
    </div>

    @if(session()->has('success'))
        <div class="bg-secondary-container border border-secondary-container text-on-secondary-container rounded-xl p-4 mb-6 text-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Applicant Profile (Left Col) -->
        <div class="md:col-span-2 space-y-6">
            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-6">
                <span class="text-xs font-semibold uppercase tracking-wider text-outline-variant mb-1 block">
                    Pelamar Divisi {{ $application->vacancy->division }} - {{ $application->vacancy->event->event_name }}
                </span>
                <h1 class="text-3xl font-bold text-on-surface mb-2">{{ $application->user->name }}</h1>
                <p class="text-sm text-outline-variant mb-6">{{ $application->user->email }}</p>

                <!-- Academic Info -->
                @if($application->user->studentProfile)
                    <div class="grid grid-cols-2 gap-4 pb-6 mb-6 border-b border-surface-dim text-sm">
                        <div>
                            <span class="text-xs text-outline-variant block font-semibold uppercase">NIM / ID Mahasiswa</span>
                            <strong>{{ $application->user->studentProfile->student_id }}</strong>
                        </div>
                        <div>
                            <span class="text-xs text-outline-variant block font-semibold uppercase">Program Studi / Fakultas</span>
                            <strong>{{ $application->user->studentProfile->study_program }} ({{ $application->user->studentProfile->faculty }})</strong>
                        </div>
                        <div>
                            <span class="text-xs text-outline-variant block font-semibold uppercase">Angkatan</span>
                            <strong>{{ $application->user->studentProfile->entry_year }}</strong>
                        </div>
                        <div>
                            <span class="text-xs text-outline-variant block font-semibold uppercase">Bio Singkat</span>
                            <span class="text-on-surface-variant italic">{{ $application->user->studentProfile->bio ?? '-' }}</span>
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-surface-container rounded-lg text-xs text-outline-variant mb-6">
                        Mahasiswa belum melengkapi profil akademik mereka.
                    </div>
                @endif

                <!-- Candidate Skills -->
                <div class="mb-6">
                    <h3 class="text-sm font-semibold text-outline-variant uppercase mb-3">Keahlian Mahasiswa</h3>
                    <div class="flex flex-wrap gap-2">
                        @forelse($application->user->skills as $sk)
                            <span class="text-xs bg-surface-container text-primary border border-primary-fixed-dim px-2.5 py-1 rounded-md font-medium">
                                {{ $sk->skill_name }}
                            </span>
                        @empty
                            <span class="text-xs text-outline-variant italic">Belum mencantumkan keahlian khusus.</span>
                        @endforelse
                    </div>
                </div>

                <!-- Experiences -->
                <div>
                    <h3 class="text-sm font-semibold text-outline-variant uppercase mb-3">Riwayat Pengalaman</h3>
                    <div class="space-y-3">
                        @forelse($application->user->experiences as $exp)
                            <div class="p-3 bg-surface-container rounded-lg border border-surface-dim text-xs">
                                <div class="font-bold text-on-surface mb-0.5">{{ $exp->title }}</div>
                                <p class="text-on-surface-variant leading-normal">{{ $exp->description }}</p>
                            </div>
                        @empty
                            <span class="text-xs text-outline-variant italic">Belum ada riwayat pengalaman kepanitiaan/organisasi.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- CV / Attachment View -->
            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-6 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-on-surface text-sm mb-1">Tautan Lampiran CV / Berkas Pendukung</h3>
                    <p class="text-xs text-outline-variant">Buka berkas di tab baru untuk meninjau kualifikasi pelamar.</p>
                </div>
                <a 
                    href="{{ route('applications.download', $application) }}" 
                    target="_blank" 
                    class="bg-primary hover:bg-primary-container text-white font-semibold text-xs px-4 py-2.5 rounded-lg transition-colors inline-flex items-center gap-1.5"
                >
                    Buka Berkas <svg class="w-4 h-4 ml-1 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                </a>
            </div>
        </div>

        <!-- Evaluation Form (Right Col) -->
        <div>
            <div class="bg-surface-container-lowest rounded-xl shadow-sm border border-surface-dim p-6 sticky top-6">
                <h3 class="text-lg font-bold text-on-surface mb-4">Formulir Evaluasi</h3>
                <form wire:submit.prevent="saveEvaluation" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-outline-variant uppercase mb-2">Ubah Status</label>
                        <select 
                            wire:model.live="status" 
                            class="w-full border border-surface-dim rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                        >
                            <option value="pending">Ditinjau (Pending)</option>
                            <option value="interviewing">Wawancara (Interviewing)</option>
                            <option value="accepted">Diterima (Accepted)</option>
                            <option value="rejected">Ditolak (Rejected)</option>
                        </select>
                    </div>

                    @if($status === 'interviewing')
                        <div class="space-y-4 border-t border-surface-dim pt-4">
                            <div>
                                <label class="block text-xs font-semibold text-outline-variant uppercase mb-2">Tanggal & Waktu Wawancara</label>
                                <input 
                                    type="datetime-local" 
                                    wire:model="interview_scheduled_at" 
                                    class="w-full border border-surface-dim rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                />
                                @error('interview_scheduled_at')
                                    <span class="text-xs text-error block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-outline-variant uppercase mb-2">Format Wawancara</label>
                                <select 
                                    wire:model="interview_format" 
                                    class="w-full border border-surface-dim rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                >
                                    <option value="online">Online</option>
                                    <option value="offline">Offline</option>
                                </select>
                                @error('interview_format')
                                    <span class="text-xs text-error block mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-outline-variant uppercase mb-2">Lokasi / Tautan Wawancara</label>
                                <input 
                                    type="text" 
                                    wire:model="interview_location" 
                                    placeholder="Link Zoom/Meet atau nama ruang wawancara..." 
                                    class="w-full border border-surface-dim rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                />
                                @error('interview_location')
                                    <span class="text-xs text-error block mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-semibold text-outline-variant uppercase mb-2">Catatan Evaluasi / Alasan Penolakan</label>
                        <textarea 
                            wire:model="feedback" 
                            rows="4" 
                            placeholder="Catatan dari panitia untuk mahasiswa..." 
                            class="w-full border border-surface-dim rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                        ></textarea>
                        @error('feedback')
                            <span class="text-xs text-error block mt-1">{{ $message }}</span>
                        @enderror
                    </div>

                    <button 
                        type="submit" 
                        class="w-full bg-primary hover:bg-primary-container text-white font-semibold py-2.5 rounded-lg text-sm transition-colors shadow-sm"
                    >
                        Simpan Evaluasi
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
