<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    //
}; ?>

<div>
    <h1>{{ __('Buat Kegiatan Baru') }}</h1>
    <p>{{ __('Formulir pendaftaran event baru.') }}</p>
</div>
