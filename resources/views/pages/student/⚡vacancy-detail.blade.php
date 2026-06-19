<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $vacancyId;

    public function mount(string $vacancy): void
    {
        $this->vacancyId = $vacancy;
    }
}; ?>

<div>
    <h1>{{ __('Detail Lowongan Kepanitiaan') }}</h1>
    <p>{{ __('Melihat detail divisi, tugas, dan syarat keahlian untuk lowongan ID: ') }}{{ $vacancyId }}</p>
</div>
