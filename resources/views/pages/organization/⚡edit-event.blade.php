<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    public string $eventId;

    public function mount(string $event): void
    {
        $this->eventId = $event;
    }
}; ?>

<div>
    <h1>{{ __('Sunting Kegiatan') }}</h1>
    <p>{{ __('Ubah rincian kegiatan dengan ID: ') }}{{ $eventId }}</p>
</div>
