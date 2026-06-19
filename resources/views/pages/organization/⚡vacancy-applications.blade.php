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
    <h1>{{ __('Pelamar Divisi Kepanitiaan') }}</h1>
    <p>{{ __('Daftar pelamar khusus untuk lowongan ID: ') }}{{ $vacancyId }}</p>
</div>
