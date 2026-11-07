<?php

use Livewire\Component;
use App\Models\Chapter;
use App\Models\Story;
use Livewire\Computed;
use App\Models\ChapterItem;
use App\Data\ItemData;

new class extends Component
{
    public Chapter $chapter;
    public Story $story;

    public bool $preview = false;
    public string $previewContent = "";

    public array $items = [];

    public bool $showEditPrompt = false;
    public string $promptEditInput = "";
    public int $promptEditIndex = 0;

    public function mount() {

        $items = [];

        foreach($this->chapter->items as $item) {
            $items[] = ItemData::from($item);
        }

        $this->items = $items;
    }

    #[Computed]
    public function words() {
        return collect($this->items)->sum(function($item) { return Str::wordCount($item->content); });
    }

    public function addItem() {
        $this->items[] = new ItemData("", "", count($this->items)+1-1);
    }

    public function togglePreview()
    {
        if (!$this->preview) {
            $this->preview = true;

            foreach($this->items as $index => $item) {
                $this->previewContent .= $item->content;
            }
        } else {
            $this->preview = false;
            $this->previewContent = "";
        }
    }

    public function saveChapter() {
        // TODO save the chapter item changes
    }

    public function editPrompt($index)
    {
        $this->showEditPrompt = true;
        $this->promptEditInput = $this->items[$index]->prompt;
        $this->promptEditIndex = $index;
    }

    public function savePrompt()
    {
        $this->items[$this->promptEditIndex]->prompt = $this->promptEditInput;
        $this->showEditPrompt = false;
    }
};
?>

<div>
    <div class="text-2xl mb-3">{{ $chapter->title }}</div>
    <div class="flex gap-3">
        <div class="text-gray-500">#{{ $chapter->ordering }}</div>
        <div class="">
            Story: <a href="{{ route('chapters', $story->id) }}" class="text-accent text-sm" wire:navigate>{{ $story->title }}</a>
        </div>
        <div class="text-gray-500">{{ count($this->items) }} items</div>
        <div class="text-gray-500">{{ $this->words() }} words</div>
    </div>
    <div class="my-3 flex items-center">
        <div class="flex-1 flex gap-3 items-center">
            @if(!$preview)
            <x-button class="btn-primary btn-sm" wire:click="addItem">Add Item</x-button>
            <x-button class="btn-secondary btn-outline btn-sm" wire:click="togglePreview" spinner="togglePreview">Preview</x-button>
            @else
            <x-button class="btn-secondary btn-outline btn-sm" wire:click="togglePreview" spinner="togglePreview">Continue Edit</x-button>
            @endif
            <x-button class="btn-primary btn-sm">Save Draft</x-button>
            <x-button class="btn-soft btn-outline btn-sm">Select Preset</x-button>
        </div>
        <div class="flex gap-3 items-center">
            <div class="">Export as:</div>
            <x-button class="btn-accent btn-outline btn-sm" wire:click="showItemForm = true">PDF</x-button>
            <x-button class="btn-accent btn-outline btn-sm" wire:click="showItemForm = true">DOCX</x-button>
            <x-button class="btn-accent btn-outline btn-sm" wire:click="showItemForm = true">ODT</x-button>
        </div>
    </div>

    <x-modal wire:model="showEditPrompt">
        <x-form>
            <x-textarea label="Prompt" wire:model="promptEditInput" rows="10" />
            <x-slot:actions>
                <x-button label="Cancel" wire:click="showEditPrompt = false" />
                <x-button label="Save" class="btn-primary" wire:click="savePrompt" />
            </x-slot:actions>
        </x-form>
    </x-modal>

    <div class="flex gap-3 my-3">
        <div class="w-2/3">
            @if(!$preview)
                    @if ($this->items)
                        <div class="flex flex-col gap-3">
                            @foreach($this->items as $index => $item)
                                <div class="rounded border border-gray-300 p-5 flex flex-col gap-3">
                                    <div class="flex gap-3 items-center">
                                        <div>#{{ $item->ordering }}</div>
                                        <div class="flex gap-3 items-center">
                                            @if($item->prompt)
                                                <div class="font-mono">{{ $item->prompt }}</div>
                                                <x-button class="btn-primary btn-xs">Generate</x-button>
                                            @else
                                                <div class="font-mono text-gray-500">You haven't write the prompt</div>
                                            @endif
                                            <x-button class="btn-outline btn-secondary btn-xs" wire:click="editPrompt({{ $index }})" spinner="editPrompt">Edit Prompt</x-button>
                                        </div>
                                    </div>
                                    @if($item->content)
                                        <div class="border-t border-t-gray-300 pt-3">{{ $item->content }}</div>
                                        <div class="flex gap-3">
                                            <x-button class="btn-outline btn-ghost btn-xs">Regenerate Result</x-button>
                                            <x-button class="btn-outline btn-ghost btn-xs">Limited Regeneration Result</x-button>
                                            <x-button class="btn-outline btn-ghost btn-xs">Clear</x-button>
                                        </div>
                                    @else
                                    <div class="border-t border-t-gray-300 pt-3 text-gray-500 italic">There is no content yet</div>
                                    @endif
                                    <div class="flex gap-1">
                                        <div class="flex-1 flex gap-1">
                                            <x-button class="btn-outline btn-accent btn-xs" wire:click="editPrompt({{ $index }})" spinner="editPrompt">Edit Prompt</x-button>
                                            @if($item->content)
                                            <x-button class="btn-outline btn-accent btn-xs">Edit Result</x-button>
                                            @endif
                                            <x-button class="btn-soft btn-xs">Up</x-button>
                                            <x-button class="btn-soft btn-xs">Down</x-button>
                                        </div>
                                        <x-button class="btn-outline btn-error btn-xs">Delete</x-button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-gray-500">There are no item yet.</div>
                    @endif
                @else
                    <div class="text-gray-500 mb-3 italic">Preview mode</div>
                    <div class="">{{ $this->previewContent }}</div>
                @endif
        </div>

        <div class="w-1/3 flex flex-col gap-3">
            {{-- sidebar --}}
            {{-- TODO presets --}}
            {{-- TODO provider selection --}}
            @livewire('providers-selection')
        </div>
    </div>

</div>
