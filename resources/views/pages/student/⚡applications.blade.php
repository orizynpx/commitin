<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.app')] class extends Component
{
    //
}; ?>

<div>
    <h1>{{ __('Lamaran Saya') }}</h1>
    <p>{{ __('Daftar status lamaran kepanitiaan Anda.') }}</p>
</div>
