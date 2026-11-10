<?php

use Livewire\Component;
use App\Models\Chapter;
use App\Models\Story;
use Livewire\Computed;
use App\Models\ChapterItem;
use App\Data\ItemData;
use App\Models\AiPreset;
use App\Models\AiProvider;
use Prism\Prism;

new class extends Component
{
    public Chapter $chapter;
    public Story $story;

    public bool $preview = false;
    public string $previewContent = "";

    public array $items = [];

    /** @var array<int, bool> per-item generation state */
    public array $generating = [];

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
        $this->items[] = new ItemData("", "", count($this->items));
    }

    public function togglePreview()
    {
        if (!$this->preview) {
            $this->preview = true;

            $this->previewContent = collect($this->items)
                ->pluck('content')
                ->filter(fn ($content) => trim((string) $content) !== '')
                ->join("\n\n");
        } else {
            $this->preview = false;
            $this->previewContent = "";
        }
    }

    public function saveChapter() {
        $this->persist();
    }

    protected function persist(): void
    {
        $this->chapter->items = collect($this->items)
            ->values()
            ->map(fn ($item) => $item->toArray())
            ->all();

        $this->chapter->save();
    }

    public function generate(int $index)
    {
        $this->resetErrorBag("item{$index}Prompt");
        $this->resetErrorBag("item{$index}Content");

        $item = $this->items[$index];

        if (trim($item->prompt) === '') {
            $this->addError("item{$index}Prompt", 'Write a prompt first.');
            return;
        }

        $provider = AiProvider::where('selected', true)->first();
        $preset = AiPreset::where('selected', true)->first();

        if (! $provider) {
            $this->addError("item{$index}Content", 'Please select a provider first.');
            return;
        }

        // TODO: remove this dump once generation is enabled.
        dd([
            'index' => $index,
            'provider' => $provider->only(['id', 'name', 'url', 'model']),
            'systemPrompt' => $preset?->prompt ?? '',
            'prompt' => $item->prompt,
        ]);

        $this->generating[$index] = true;

        try {
            $this->items[$index]->content = Prism::text()
                ->using($provider->name, $provider->model, [
                    'api_key' => $provider->api_key,
                    'url' => $provider->url,
                ])
                ->withSystemPrompt($preset?->prompt ?? '')
                ->withPrompt($item->prompt)
                ->asText()
                ->text;
        } catch (\Throwable $e) {
            report($e);
            $this->addError("item{$index}Content", $e->getMessage());
        } finally {
            $this->generating[$index] = false;
        }

        $this->persist();
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

    public function deleteItem($index) {
        array_splice($this->items, $index, 1);
        $this->reindex();
    }

    public function moveItem(int $index, int $direction): void
    {
        $target = $index + $direction;

        if (! isset($this->items[$target])) {
            return;
        }

        [$this->items[$index], $this->items[$target]] = [$this->items[$target], $this->items[$index]];

        $this->reindex();
    }

    protected function reindex(): void
    {
        $this->items = array_values($this->items);

        foreach ($this->items as $position => $item) {
            $item->ordering = $position;
        }
    }
};
?>

<div>
    <div class="text-sm"><a href="{{ route('chapters', $story->id) }}" class="text-accent text-sm" wire:navigate>&larr; Back to story</a> | {{ $story->title }}</div>
    <div class="text-2xl mb-3">{{ $chapter->title }}</div>
    <div class="flex gap-3">
        <div class="text-gray-500">Chapter #{{ $chapter->ordering }}</div>
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
            <x-button class="btn-primary btn-sm" wire:click="saveChapter" spinner="saveChapter">Save</x-button>
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
                                <x-card
                                    wire:key="editor-{{ $item->key }}"
                                class="bg-base-200"
                                x-data="{ showPromptEditor: false}"
                                x-key="item-{{ $item->key }}"
                                >
                                    <div class="flex gap-3 items-start">
                                        <div class="text-lg font-bold">#{{ str_pad($item->ordering, 2, "0", STR_PAD_LEFT) }}</div>
                                        <div class="flex-1 flex flex-col gap-3">
                                            <div x-show="showPromptEditor" style="display: none" class="flex flex-col gap-2">
                                                <x-textarea
                                                    class="textarea-ghost focus:outline-none bg-base-300"
                                                    placeholder="Your prompt here"
                                                    rows="4"
                                                    wire:model.blur="items.{{ $index }}.prompt"
                                                />
                                                @error("item{$index}Prompt")
                                                    <div class="text-error text-xs">{{ $message }}</div>
                                                @enderror
                                                <div class="flex items-center justify-end gap-2">
                                                    <div wire:loading wire:target="generate({{ $index }})" class="text-xs text-orange-500">Generating...</div>
                                                    <x-button
                                                        class="btn-primary btn-xs"
                                                        wire:click="generate({{ $index }})"
                                                        wire:loading.attr="disabled"
                                                        wire:target="generate({{ $index }})"
                                                    >Generate</x-button>
                                                </div>
                                            </div>
                                            @if($item->content)
                                                <div class="whitespace-pre-wrap">{{ $item->content }}</div>
                                            @else
                                                <div class="text-gray-500 italic">There is no content yet</div>
                                            @endif
                                            @error("item{$index}Content")
                                                <div class="text-error text-xs">{{ $message }}</div>
                                            @enderror
                                            <div class="flex gap-1">
                                                <div class="flex-1 flex gap-1 items-center">
                                                    <x-button class="btn-soft btn-xs" x-on:click="showPromptEditor = !showPromptEditor">
                                                        <span x-show="!showPromptEditor">Prompt</span>
                                                        <span x-show="showPromptEditor" style="display: none">Hide Prompt</span>
                                                    </x-button>
                                                    @if($item->content)
                                                        <x-button class="btn-outline btn-accent btn-xs">Edit Result</x-button>
                                                    @endif
                                                    <x-button class="btn-soft btn-xs" wire:click="moveItem({{ $index }}, -1)" :disabled="$loop->first">Up</x-button>
                                                    <x-button class="btn-soft btn-xs" wire:click="moveItem({{ $index }}, 1)" :disabled="$loop->last">Down</x-button>
                                                </div>
                                                <x-button class="btn-outline btn-error btn-xs" wire:confirm="Are you sure?" wire:click="deleteItem({{ $index }})">Delete</x-button>
                                            </div>
                                        </div>
                                    </div>
                                </x-card>
                            @endforeach
                        </div>
                    @else
                        <div class="text-gray-500">There are no item yet.</div>
                    @endif
                @else
                    <div class="text-gray-500 mb-3 italic">Preview mode</div>
                    <div class="whitespace-pre-wrap">{!! nl2br(e($this->previewContent)) !!}</div>
                @endif
        </div>

        <div class="w-1/3 flex flex-col gap-3">
            @livewire('providers-selection')
            @livewire('preset-selection')
        </div>
    </div>

</div>
