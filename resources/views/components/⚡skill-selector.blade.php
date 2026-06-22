<?php

use App\Models\Skill;
use Livewire\Attributes\Modelable;
use Livewire\Component;

new class extends Component {
    #[Modelable]
    public array $selectedIds = [];

    public string $search = '';
    public bool $allowSuggest = true;

    public function selectSkill(string $id): void
    {
        if (!in_array($id, $this->selectedIds)) {
            $this->selectedIds[] = $id;
        }
        $this->search = '';
    }

    public function removeSkill(string $id): void
    {
        $this->selectedIds = array_values(array_diff($this->selectedIds, [$id]));
    }

    public function suggestSkill(): void
    {
        $name = trim($this->search);
        if ($name === '') {
            return;
        }

        $existing = Skill::where('skill_name', $name)->first();
        if ($existing) {
            if ($existing->status === 'approved') {
                $this->selectSkill($existing->skill_id);
            } else {
                session()->flash('status', 'Keahlian ini sudah diusulkan sebelumnya dan sedang menunggu persetujuan.');
            }
            $this->search = '';
            return;
        }

        Skill::create([
            'skill_name' => $name,
            'status' => 'pending',
        ]);

        session()->flash('status', 'Keahlian baru berhasil diusulkan dan menunggu persetujuan admin.');
        $this->search = '';
    }
}; ?>

<div class="space-y-3 relative" x-data="{ open: false }">
    <div class="flex flex-wrap gap-2 mb-2">
        @foreach($selectedIds as $id)
            @php
                $skill = \App\Models\Skill::find($id);
            @endphp
            @if($skill)
                <span class="bg-surface-container border border-surface-dim text-on-surface px-3 py-1 rounded-full text-sm font-bold flex items-center gap-1.5">
                    {{ $skill->skill_name }}
                    <button type="button" wire:click="removeSkill('{{ $skill->skill_id }}')" class="text-error hover:text-error-container font-black focus:outline-none ml-1" aria-label="Hapus keahlian {{ $skill->skill_name }}">&times;</button>
                </span>
            @endif
        @endforeach
    </div>

    <div class="relative">
        <input 
            type="text" 
            wire:model.live="search" 
            @focus="open = true"
            @click.away="open = false"
            placeholder="Ketik untuk mencari keahlian..." 
            class="w-full border border-surface-dim rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary bg-surface-container-lowest" 
        />

        @if($search !== '')
            @php
                $matches = \App\Models\Skill::where('status', 'approved')
                    ->where('skill_name', 'like', '%' . $search . '%')
                    ->whereNotIn('skill_id', $selectedIds)
                    ->limit(10)
                    ->get();
                $exactApprovedMatch = \App\Models\Skill::where('status', 'approved')
                    ->where('skill_name', trim($search))
                    ->exists();
            @endphp

            <div x-show="open" style="display: none;" class="absolute left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-surface-container-lowest border border-surface-dim rounded-lg shadow-lg z-50 divide-y divide-surface-dim">
                @foreach($matches as $m)
                    <button 
                        type="button" 
                        wire:click="selectSkill('{{ $m->skill_id }}')" 
                        @click="open = false"
                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-surface-container transition-colors focus:outline-none text-on-surface"
                    >
                        {{ $m->skill_name }}
                    </button>
                @endforeach

                @if($allowSuggest && !$exactApprovedMatch)
                    <button 
                        type="button" 
                        wire:click="suggestSkill" 
                        @click="open = false"
                        class="w-full text-left px-4 py-2.5 text-sm hover:bg-surface-container transition-colors text-primary font-semibold focus:outline-none"
                    >
                        {{ __('Usulkan ":search" sebagai keahlian baru', ['search' => $search]) }}
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>
