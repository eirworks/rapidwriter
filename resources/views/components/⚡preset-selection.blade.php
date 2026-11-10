<?php

use App\Models\AiPreset;
use Livewire\Component;

new class extends Component {
    public $presets = null;

    public ?AiPreset $selectedPreset;
    public int $selectedPresetId = 0;

    public function mount()
    {
        $this->presets = AiPreset::all();

        $selectedPreset = AiPreset::where('selected', true)->first();
        $this->selectedPreset = $selectedPreset;
        $this->selectedPresetId = $selectedPreset->id ?? 0;
    }

    public function updated($property, $value)
    {
        if ($property === 'selectedPresetId') {
            AiPreset::where('selected', true)->update(['selected' => false]);
            AiPreset::where('id', $value)->update(['selected' => true]);
            $this->selectedPreset = AiPreset::where('selected', true)->first();
        }
    }
};
?>

<div>
    <x-card class="bg-base-200">
        @if(!$selectedPresetId)
            <x-alert class="alert-warning">Please select a system prompt</x-alert>
        @endif
        <div class="text-orange-500 text-sm float-right" wire:loading>Please wait</div>
        <x-select :options="$presets" wire:model.live="selectedPresetId" label="Select Preset"/>
        <div class="mt-3">
            <x-textarea readonly rows="10" label="System Prompt" placeholder="No system prompt">
                {{ $selectedPreset->prompt ?? "" }}
            </x-textarea>
        </div>
    </x-card>
</div>
