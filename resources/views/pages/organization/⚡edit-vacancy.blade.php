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
    <h1>{{ __('Sunting Lowongan Divisi') }}</h1>
    <p>{{ __('Ubah rincian lowongan dengan ID: ') }}{{ $vacancyId }}</p>
</div>
