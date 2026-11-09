<?php

use App\Models\AiPreset;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;

use Livewire\Component;

new class extends Component {
    use WithPagination;

    public bool $showForm = false;
    public int $presetId = 0;
    #[\Livewire\Attributes\Validate('required')]
    public string $name;
    #[\Livewire\Attributes\Validate('required')]
    public string $prompt;

    #[Computed]
    public function presets()
    {
        return AiPreset::latest('id')->paginate();
    }

    public function createPreset()
    {
        $this->name = "";
        $this->prompt = "";

        $this->showForm = true;
    }

    public function editPreset($id)
    {
        $preset = AiPreset::find($id);
        $this->presetId = $id;
        $this->name = $preset->name;
        $this->prompt = $preset->prompt;

        $this->showForm = true;
    }

    public function savePreset()
    {
        $this->validate();

        if ($this->presetId)
        {
            $preset = AiPreset::find($this->presetId);

            $preset->name = $this->name;
            $preset->prompt = $this->prompt;
        } else {
            $preset = new AiPreset();
            $preset->name = $this->name;
            $preset->prompt = $this->prompt;

        }

        $preset->save();
        $this->showForm = false;
    }

    public function deletePreset($id)
    {
        $preset = AiPreset::find($id);
        $preset->delete();
    }
};
?>

<div>
    <div class="mb-1"><a href="{{ route('home') }}" wire:navigate class="text-accent text-sm">&larr; Return to Stories</a></div>

    <div class="text-2xl mb-3">Presets & System Prompt</div>

    <div class="flex gap-3 items-center mb-3">
        <x-button class="btn-primary" wire:click="createPreset">New Preset</x-button>
    </div>

    <div class="flex gap-3">

        <div class="md:w-1/2">
            @if($this->presets()->count())
                <div class="flex flex-col gap-3">
                    @foreach ($this->presets() as $preset)
                        <div class="p-3 border rounded border-gray-500">
                            <div>{{ $preset->name }}</div>
                            <div class="text-sm text-muted">
                                {{ Str::limit($preset->prompt) }}
                            </div>
                            <div class="mt-3 flex gap-1">
                                <div class="flex-1">
                                    <x-button class="btn-soft btn-xs" wire:click="editPreset({{ $preset->id }})">Edit</x-button>
                                </div>
                                <div>
                                    <x-button class="btn-outline btn-error btn-xs" wire:confirm="Are you sure?" wire:click="deletePreset({{ $preset->id }})">Remove</x-button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-muted">
                    You don't have any presets.
                </div>
            @endif
        </div>

        <x-modal wire:model="showForm" :title="$presetId ? 'Edit Preset' : 'New Preset'">
            <x-form wire:submit="savePreset">
                <x-input wire:model="name" label="Preset Name" />
                <x-textarea wire:model="prompt" label="System Prompt" rows="10" />

                <x-slot:actions>
                    <x-button class="btn-soft" type="button" wire:click="showForm = false">Cancel</x-button>
                    <x-button class="btn-primary" type="submit">Save</x-button>
                </x-slot:actions>
            </x-form>
        </x-modal>
    </div>
</div>
