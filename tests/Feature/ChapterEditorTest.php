<?php

use App\Models\Chapter;
use App\Models\Story;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('renders the chapter editor', function () {
    $story = Story::factory()->create();
    $chapter = Chapter::factory()->create(['story_id' => $story->id, 'ordering' => 1]);
    $chapter->items = [
        ['prompt' => 'first prompt', 'content' => 'first content', 'ordering' => 0],
        ['prompt' => '', 'content' => '', 'ordering' => 1],
    ];
    $chapter->save();

    $response = $this->get(route('chapters.editor', ['story' => $story->id, 'chapter' => $chapter->id]));
    $response->assertOk();
    $response->assertSee('first prompt');

    // prompt field + generate button are rendered hidden, toggled client-side by the "Prompt" button
    $response->assertSee('x-show="showPromptEditor"', false);
    $response->assertSee('>Prompt<', false);
    $response->assertSee('>Hide Prompt<', false);
    $response->assertSee('wire:model.blur="items.0.prompt"', false);
    $response->assertSee('wire:click="generate(0)"', false);
});

it('binds prompts, deletes and reorders items', function () {
    $story = Story::factory()->create();
    $chapter = Chapter::factory()->create(['story_id' => $story->id, 'ordering' => 1]);
    $chapter->items = [
        ['prompt' => 'a', 'content' => '', 'ordering' => 0],
        ['prompt' => 'b', 'content' => '', 'ordering' => 1],
        ['prompt' => 'c', 'content' => '', 'ordering' => 2],
    ];
    $chapter->save();

    $c = Livewire::test('pages::stories.chapter-editor', ['story' => $story, 'chapter' => $chapter])
        ->assertOk();

    // bind a prompt through the same dot path the textarea uses
    $c->set('items.0.prompt', 'edited')
        ->assertSet('items.0.prompt', 'edited')
        ->assertSet('items.0.key', fn ($key) => is_string($key) && $key !== '');

    // delete middle item -> reindexed, ordering renumbered
    $c->call('deleteItem', 1)
        ->assertCount('items', 2)
        ->assertSet('items.0.prompt', 'edited')
        ->assertSet('items.1.prompt', 'c')
        ->assertSet('items.1.ordering', 1);

    // move
    $c->call('moveItem', 0, 1)
        ->assertSet('items.0.prompt', 'c')
        ->assertSet('items.1.prompt', 'edited');

    // add + save
    $c->call('addItem')->assertCount('items', 3);
    $c->call('saveChapter');

    $fresh = $chapter->fresh();
    expect($fresh->items)->toHaveCount(3)
        ->and($fresh->items[0]['prompt'])->toBe('c')
        ->and($fresh->items[2]['ordering'])->toBe(2)
        ->and($fresh->items[0])->toHaveKey('key');
});

it('validates before generating', function () {
    $story = Story::factory()->create();
    $chapter = Chapter::factory()->create(['story_id' => $story->id, 'ordering' => 1]);
    $chapter->items = [['prompt' => '', 'content' => '', 'ordering' => 0]];
    $chapter->save();

    Livewire::test('pages::stories.chapter-editor', ['story' => $story, 'chapter' => $chapter])
        ->call('generate', 0)
        ->assertHasErrors('item0Prompt');
});
