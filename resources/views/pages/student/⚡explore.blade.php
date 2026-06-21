<?php

use Livewire\Attributes\Layout;
use Livewire\Component;
use App\Models\Vacancy;
use App\Models\Skill;

new #[Layout('layouts.app')] class extends Component
{
    public $search = '';
    public $selectedSkill = '';

    public function render()
    {
        $userSkills = auth()->user()->skills->pluck('skill_id')->toArray();

        $query = Vacancy::with(['event.organizers', 'skills'])
            ->where('status', 'OPEN');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('division', 'like', '%' . $this->search . '%')
                  ->orWhereHas('event', function($eq) {
                      $eq->where('event_name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if (!empty($this->selectedSkill)) {
            $query->whereHas('skills', function($q) {
                $q->where('skills.skill_id', $this->selectedSkill);
            });
        }

        $vacancies = $query->get()->map(function ($vacancy) use ($userSkills) {
            $reqSkills = $vacancy->skills->pluck('skill_id')->toArray();
            $matched = count(array_intersect($userSkills, $reqSkills));
            $totalReq = count($reqSkills);
            $vacancy->match_count = $matched;
            $vacancy->total_req_skills = $totalReq;
            $vacancy->is_recommended = ($totalReq > 0 && ($matched / $totalReq) >= 0.5);
            return $vacancy;
        });

        $vacancies = $vacancies->sortByDesc('is_recommended');

        return view('pages.student.⚡explore', [
            'vacancies' => $vacancies,
            'skills' => Skill::where('status', 'approved')->get(),
        ]);
    }
}; ?>

<div class="max-w-6xl mx-auto py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-on-surface mb-2">{{ __('Eksplorasi Lowongan Kepanitiaan') }}</h1>
        <p class="text-on-surface-variant">{{ __('Cari dan temukan lowongan panitia yang sesuai dengan keahlian Anda.') }}</p>
    </div>

    <div class="bg-surface-container-lowest rounded-lg shadow-sm border border-surface-dim p-6 mb-8 flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <label class="block text-xs font-semibold text-outline-variant uppercase mb-2">Cari Divisi atau Kegiatan</label>
            <input 
                type="text" 
                wire:model.live="search" 
                placeholder="Cari berdasarkan divisi (misal: Dokumentasi) atau nama event..." 
                class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
            />
        </div>
        <div class="w-full md:w-64">
            <label class="block text-xs font-semibold text-outline-variant uppercase mb-2">Filter Keahlian</label>
            <select 
                wire:model.live="selectedSkill" 
                class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary"
            >
                <option value="">Semua Keahlian</option>
                @foreach($skills as $skill)
                    <option value="{{ $skill->skill_id }}">{{ $skill->skill_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($vacancies as $vacancy)
            @php
                $creator = $vacancy->event->organizers->firstWhere('pivot.organizer_role', 'creator') 
                        ?? $vacancy->event->organizers->firstWhere('pivot.organizer_role', 'owner') 
                        ?? $vacancy->event->organizers->first();
                $creatorName = $creator ? $creator->name : 'N/A';
            @endphp
            <div class="bg-surface-container-lowest rounded-lg shadow-sm border border-surface-dim p-6 flex flex-col justify-between hover:shadow-md transition-shadow relative">

                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-primary mb-1 block">
                        {{ $vacancy->event->event_name }} (Penyelenggara: {{ $creatorName }})
                    </span>
                    <h3 class="text-xl font-bold text-on-surface mb-2">{{ $vacancy->division }}</h3>
                    <p class="text-on-surface-variant text-sm mb-4 line-clamp-3">{{ $vacancy->vacancy_description }}</p>

                    <div class="mb-6">
                        <span class="block text-xs font-semibold text-outline-variant uppercase mb-2">Keahlian Dibutuhkan:</span>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse($vacancy->skills as $vsk)
                                @php
                                    $hasSkill = in_array($vsk->skill_id, auth()->user()->skills->pluck('skill_id')->toArray());
                                @endphp
                                <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded {{ $hasSkill ? 'bg-primary text-white border border-primary' : 'bg-surface-container text-on-surface-variant border border-surface-dim' }}">
                                    {{ $vsk->skill_name }}
                                </span>
                            @empty
                                <span class="text-xs text-outline-variant italic">Tidak ada keahlian khusus</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-surface-dim flex items-center justify-between">
                    <span class="text-xs text-outline-variant">
                        Kecocokan keahlian: <strong>{{ $vacancy->match_count }}/{{ $vacancy->total_req_skills }}</strong>
                    </span>
                    <a 
                        href="{{ route('vacancies.show', $vacancy->vacancy_id) }}" 
                        class="bg-primary hover:bg-primary-container text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors"
                    >
                        Lihat Detail
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-surface-container-lowest rounded-lg shadow-sm border border-surface-dim p-12 text-center">
                <svg class="w-16 h-16 text-outline-variant mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <h3 class="text-lg font-bold text-on-surface mb-1">Tidak Ada Lowongan</h3>
                <p class="text-outline-variant text-sm">Tidak menemukan lowongan kepanitiaan aktif dengan kriteria pencarian tersebut.</p>
            </div>
        @endforelse
    </div>
</div>
