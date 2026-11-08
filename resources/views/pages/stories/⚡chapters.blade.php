<?php

use Livewire\Component;
use App\Models\Story;
use Livewire\Computed;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

new class extends Component
{
    use WithPagination;

    public int $storyId = 0;

    public ?Story $story = null;

    public bool $showChapterForm = false;

    #[Validate('required')]
    public string $title = "";
    #[Validate('integer')]
    public int $ordering = 0;

    public function mount(Story $story) {
        $this->story = $story;
    }

    #[Computed]
    public function chapters()
    {
        return $this->story->chapters()->latest('ordering')->orderBy('id', 'desc')->paginate();
    }

    public function saveChapter() {
        $this->validate();

        $this->story->chapters()->create([
            'title' => $this->title,
            'ordering' => $this->ordering,
            'compiled_content' => "",
        ]);

        $this->showChapterForm = false;
    }
};
?>

<div>
    <div class="mb-3"><a href="{{ route('home') }}" class="text-accent text-sm" wire:navigate>&larr; Back to stories</a></div>
    <div class="text-2xl font-bold mb-3">{{ $story->title }}</div>
    <div class="mb-5 border-b border-b-gray-500 pb-3">{{ $story->description }}</div>
    <div class="flex gap-3 items-center mb-3">
        <div class="font-bold">Chapters ({{ $this->chapters()->total() }})</div>
        <div class=""><x-button class="btn-primary btn-sm" spinner wire:click="showChapterForm = true">New Chapter</x-button></div>
        <div class=""><x-button class="btn-secondary btn-outline btn-sm">Export Story</x-button></div>
    </div>
    <x-modal wire:model="showChapterForm" title="New Chapter">
        <x-form wire:submit="saveChapter">
            <x-input label="Title" wire:model="title" />
            <x-input label="Ordering" wire:model="ordering" type="number" />

            <x-slot:actions>
                <x-button label="Cancel" wire:click="showChapterForm = false" />
                <x-button label="Create" class="btn-primary" type="submit" spinner="saveChapter" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    @if($this->chapters()->isNotEmpty())
        <div class="flex flex-col gap-1">
            @foreach ($this->chapters() as $chapter)
            <div>
                <div>
                    <a href="{{ route('chapters.editor', [$story->id, $chapter->id]) }}" class="text-accent text-sm" wire:navigate>{{ $chapter->title }}</a>
                </div>
                <div class="flex gap-3 text-sm text-gray-500">
                    <div>Chapter #{{ $chapter->ordering ?? 0 }}</div>
                    <div>{{ $chapter->words ?? 0 }} words</div>
                    <div>{{ count($chapter->items) }} items</div>
                </div>
            </div>
            @endforeach
        </div>
        {!! $this->chapters()->links() !!}
    @else
        <div class="p-3 border rounded my-3 text-gray-500 italic">This story has no chapter</div>
    @endif
</div>
