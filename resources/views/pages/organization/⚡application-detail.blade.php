<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $applicationId;

    public function mount(string $application): void
    {
        $this->applicationId = $application;
    }
}; ?>

<div>
    <h1>{{ __('Detail Evaluasi Pelamar') }}</h1>
    <p>{{ __('Evaluasi berkas dan CV untuk lamaran ID: ') }}{{ $applicationId }}</p>
</div>
