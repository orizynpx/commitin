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

        $query = Vacancy::with(['event', 'skills'])
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
            // Highlight if student has all or most (>= 50%) of required skills
            $vacancy->is_recommended = ($totalReq > 0 && ($matched / $totalReq) >= 0.5);
            return $vacancy;
        });

        // Sort recommended first
        $vacancies = $vacancies->sortByDesc('is_recommended');

        return view('pages.student.⚡explore', [
            'vacancies' => $vacancies,
            'skills' => Skill::all(),
        ]);
    }
}; ?>

<div class="max-w-6xl mx-auto py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ __('Eksplorasi Lowongan Kepanitiaan') }}</h1>
        <p class="text-gray-600">{{ __('Cari dan temukan lowongan panitia yang sesuai dengan keahlian Anda.') }}</p>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8 flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Cari Divisi atau Kegiatan</label>
            <input 
                type="text" 
                wire:model.live="search" 
                placeholder="Cari berdasarkan divisi (misal: Dokumentasi) atau nama event..." 
                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
        </div>
        <div class="w-full md:w-64">
            <label class="block text-xs font-semibold text-gray-500 uppercase mb-2">Filter Keahlian</label>
            <select 
                wire:model.live="selectedSkill" 
                class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <option value="">Semua Keahlian</option>
                @foreach($skills as $skill)
                    <option value="{{ $skill->skill_id }}">{{ $skill->skill_name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Vacancies Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($vacancies as $vacancy)
            <div class="bg-white rounded-xl shadow-sm border {{ $vacancy->is_recommended ? 'border-blue-400 bg-blue-50/10' : 'border-gray-100' }} p-6 flex flex-col justify-between hover:shadow-md transition-shadow relative">
                @if($vacancy->is_recommended)
                    <span class="absolute top-4 right-4 bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full flex items-center gap-1">
                        ✨ Sangat Cocok
                    </span>
                @endif

                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-600 mb-1 block">
                        {{ $vacancy->event->event_name }}
                    </span>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">{{ $vacancy->division }}</h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">{{ $vacancy->vacancy_description }}</p>

                    <!-- Required Skills -->
                    <div class="mb-6">
                        <span class="block text-xs font-semibold text-gray-400 uppercase mb-2">Keahlian Dibutuhkan:</span>
                        <div class="flex flex-wrap gap-1.5">
                            @forelse($vacancy->skills as $vsk)
                                @php
                                    $hasSkill = in_array($vsk->skill_id, auth()->user()->skills->pluck('skill_id')->toArray());
                                @endphp
                                <span class="text-xs px-2.5 py-1 rounded-md {{ $hasSkill ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-gray-50 text-gray-600 border border-gray-100' }}">
                                    {{ $vsk->skill_name }} {{ $hasSkill ? '✓' : '' }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-400 italic">Tidak ada keahlian khusus</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-50 flex items-center justify-between">
                    <span class="text-xs text-gray-500">
                        Kecocokan keahlian: <strong>{{ $vacancy->match_count }}/{{ $vacancy->total_req_skills }}</strong>
                    </span>
                    <a 
                        href="{{ route('vacancies.show', $vacancy->vacancy_id) }}" 
                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition-colors"
                    >
                        Lihat Detail
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <h3 class="text-lg font-bold text-gray-900 mb-1">Tidak Ada Lowongan</h3>
                <p class="text-gray-500 text-sm">Tidak menemukan lowongan kepanitiaan aktif dengan kriteria pencarian tersebut.</p>
            </div>
        @endforelse
    </div>
</div>
