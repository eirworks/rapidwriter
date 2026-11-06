<?php

use Livewire\Component;
use App\Models\Story;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;

new class extends Component
{
    use WithPagination;

    // forms
    public bool $showForm = false;
    public bool $isEdit = false;
    public int $storyId = 0;
    public string $title = "";
    public string $description = "";
    public string $slug = "";

    protected function rules() {
        return [
            'title' => 'required',
            'description' => 'nullable',
            'slug' => Rule::unique('stories', 'slug')->ignore($this->storyId),
        ];
    }

    public function mount() {
    }

    #[Computed]
    public function stories()
    {
        return Story::latest('updated_at')->paginate();
    }

    public function createStory() {
        $this->showForm = true;
        $this->isEdit = false;
        $this->storyId = 0;
        $this->title = "";
        $this->slug = "";
        $this->description = "";
    }

    public function editStory($id)
    {
        $this->showForm = true;
        $this->isEdit = true;
        $this->storyId = $id;
        $story = Story::find($id);
        $this->title = $story->title;
        $this->slug = $story->slug;
        $this->description = $story->description;
    }

    public function saveStory() {
        $this->validate();

        if ($this->storyId) {
            $story = Story::find($this->storyId);
            $story->update([
                'title' => $this->title,
                'slug' => $this->slug,
                'description' => $this->description,
            ]);
        } else {
            Story::create([
                'title' => $this->title,
                'slug' => $this->slug,
                'description' => $this->description,
            ]);
        }

        $this->showForm = false;
    }
};
?>

<div>
    <nav class="mb-4">
        <x-button class="btn-primary" wire:click="createStory" spinner>New Story</x-button>
        <button class="btn btn-accent btn-outline">Config</button>
        <button class="btn btn-accent btn-outline">Presets</button>
    </nav>
    <x-modal wire:model="showForm" wire:submit="saveStory" :title="$isEdit ? 'Edit Story' : 'New Story'">
        <x-form>
            <x-input label="Title" wire:model="title" />
            <x-input label="Slug" wire:model="slug" />
            <x-textarea label="Description (optional)" wire:model="description" />

            <x-slot:actions>
                <x-button label="Cancel" wire:click="showForm = false" />
                <x-button :label="$isEdit ? 'Update' : 'Create'" class="btn-primary" type="submit" spinner="saveStory" />
            </x-slot:actions>
        </x-form>
    </x-modal>
    @if($this->stories())
    @foreach($this->stories() as $story)
        <div class="my-3">
            <div class="flex gap-3 items-center">
                <div class="font-bold">{{ $story->title }}</div>
                <div class="text-gray-500 text-sm">{{ rand(3,15) }} chapters</div>
            </div>
            <div>{{ Str::limit($story->description, 100) }}</div>
            <div class="mt-3 flex gap-3">
                <x-button class="btn btn-outline btn-secondary btn-sm" wire:click="editStory({{ $story->id }})" spinner="editStory">Edit</x-button>
                <button class="btn btn-outline btn-accent btn-sm">Chapters</button>
            </div>
        </div>
    @endforeach
    {!! $this->stories()->links() !!}
    @else
    <div class="text-gray-500">No stories yet</div>
    @endif
</div>
