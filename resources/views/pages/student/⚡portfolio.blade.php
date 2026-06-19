<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $studentId;

    public function mount(string $student): void
    {
        $this->studentId = $student;
    }
}; ?>

<div>
    <h1>{{ __('Portofolio Mahasiswa') }}</h1>
    <p>{{ __('Melihat detail profil, keahlian, dan riwayat pengalaman mahasiswa dengan ID: ') }}{{ $studentId }}</p>
</div>
