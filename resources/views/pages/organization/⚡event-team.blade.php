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
    <h1>{{ __('Kelola Tim Kolaborator Event') }}</h1>
    <p>{{ __('Kelola tim kepanitiaan dan hak akses untuk Event ID: ') }}{{ $eventId }}</p>
</div>
