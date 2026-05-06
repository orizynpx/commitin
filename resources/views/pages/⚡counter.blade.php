<?php

use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Counter')] class extends Component {
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }

    public function decrement(): void
    {
        $this->count--;
    }
}
?>

<div>
    <h1 >Count: {{ $count }}</h1>
    <button wire:click="decrement">-</button>
    <button wire:click="increment">+</button>
</div>