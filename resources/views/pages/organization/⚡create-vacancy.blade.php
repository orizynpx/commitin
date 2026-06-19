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
    <h1>{{ __('Buat Lowongan Divisi') }}</h1>
    <p>{{ __('Buat lowongan panitia untuk Event ID: ') }}{{ $eventId }}</p>
</div>
