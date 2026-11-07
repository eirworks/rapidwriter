<?php

use Livewire\Component;
use App\Models\AiProvider;

new class extends Component
{
    public $providers = null;

    public $showProviderForm = false;
    public $providerId = 0;

    public string $name = "";
    public string $model = "";
    public string $url = "";
    public string $apiKey = "";

    public function mount()
    {
        $this->providers = AiProvider::all();
    }

    public function rules()
    {
        return [
            'name' => 'required',
            'model' => 'required',
            'url' => 'required',
            'apiKey' => 'nullable',
        ];
    }

    public function createProvider() {
        $this->showProviderForm = true;
        $this->providerId = 0;
        $this->name = "";
        $this->model = "";
        $this->url = "";
        $this->apiKey = "";
    }

    public function editProvider($id) {
        $this->showProviderForm = true;
        $this->providerId = $id;
        $provider = AiProvider::find($id);
        $this->name = $provider->name;
        $this->model = $provider->model;
        $this->url = $provider->url;
        $this->apiKey = $provider->api_key;
    }

    public function saveProvider() {
        $this->validate();

        if ($this->providerId) {
            $provider = AiProvider::find($this->providerId);
            $provider->update([
                'name' => $this->name,
                'model' => $this->model,
                'url' => $this->url,
                'api_key' => $this->apiKey,
            ]);
        } else {
            AiProvider::create([
                'name' => $this->name,
                'model' => $this->model,
                'url' => $this->url,
                'api_key' => $this->apiKey,
            ]);
        }
    }

    public function deleteProvider($id) {
        $provider = AiProvider::find($id);
        $provider->delete();
    }
};
?>

<div>
    <div class="text-2xl mb-3">Providers</div>
    <div class="mb-3">
        <x-button class="btn-primary btn-sm" wire:click="createProvider">New Provider</x-button>
    </div>
    <x-modal wire:model="showProviderForm" title="New Provider">
        <x-form wire:submit="saveProvider">
            <x-input label="Name" wire:model="name" />
            <x-input label="Model" wire:model="model" placeholder="gpt-3.5-turbo,gpt-4" hint="Comma separated models from this provider" />
            <x-input label="BaseURL" wire:model="url" placeholder="https://..." hint="Must be OpenAI compatible API" />
            <x-input label="API Key" wire:model="apiKey" placeholder="sk-..." hint="Leave empty if provider require no API Key" type="password" />

            <x-slot:actions>
                <x-button label="Cancel" wire:click="showProviderForm = false" />
                <x-button label="Create" class="btn-primary" type="submit" spinner="saveProvider" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <div class="flex flex-col gap-3">
        @foreach ($this->providers as $provider)
            <div class="rounded p-3 bg-base-200 flex gap-2 flex-col">
                <div class="font-bold">{{ $provider->name }}</div>
                <div class="text-sm text-gray-500">{{ $provider->url }}</div>
                <div class="text-sm text-gray-500">{{ $provider->model }}</div>
                <div class="flex gap-3 mt-3">
                    <div class="flex-1 flex gap-3">
                        <x-button class="btn-outline btn-accent btn-xs" wire:click="editProvider({{ $provider->id }})" spinner="editProvider">Edit</x-button>
                    </div>
                    <x-button class="btn-outline btn-error btn-xs" wire:click="deleteProvider({{ $provider->id }})" spinner="deleteProvider" wire:confirm="Are you sure?">Delete</x-button>
                </div>
            </div>
        @endforeach
    </div>
</div>
