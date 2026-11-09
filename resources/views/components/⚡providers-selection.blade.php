<?php

use Livewire\Component;
use App\Models\AiProvider;

new class extends Component
{
    public $providers = null;
    public int $selectedProvider = 0;

    public function mount()
    {
        $this->selectedProvider = AiProvider::where('selected', true)->first()->id ?? 0;
        $this->providers = AiProvider::get()->map(function($p) { return ['name' => $p->name, 'hint' => $p->model, 'id' => $p->id]; })->toArray();
    }

    public function updated($property, $value)
    {
        if ($property == 'selectedProvider') {
            AiProvider::whereNot('id', $value)->update(['selected' => false]);
            AiProvider::where('id', $value)->update(['selected' => true]);

            $this->dispatch('providerChanged', $value);
        }
    }
};
?>

<div>
    <x-card class="bg-base-200">
        @if($selectedProvider == 0)
            <x-alert class="alert-warning">Please select a provider!</x-alert>
        @endif
        <div class="text-xs text-orange-500 my-3 float-end" wire:loading>Please wait...</div>
        <x-radio wire:live label="Select Provider" wire:model.live="selectedProvider" :options="$providers" wire:loading.attr="disabled" />
    </x-card>
</div>
