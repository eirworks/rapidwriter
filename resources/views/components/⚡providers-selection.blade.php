<?php

use Livewire\Component;
use App\Models\AiProvider;

new class extends Component
{
    public $providers = null;
    public int $selectedProvider = 0;

    public function mount()
    {
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
        <x-radio wire:live label="Select Provider" wire:model.live="selectedProvider" :options="$providers" />
    </x-card>
</div>
