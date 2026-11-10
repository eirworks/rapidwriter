<?php

namespace App\Data;

use Illuminate\Support\Str;
use Livewire\Wireable;
use Spatie\LaravelData\Concerns\WireableData;
use Spatie\LaravelData\Data;

class ItemData extends Data implements Wireable
{
    use WireableData;

    public function __construct(
        public string $prompt,
        public string $content,
        public int $ordering = 0,
        public string $key = '',
    ) {
        // Stable identity for the item, so wire:key / x-key survive deletes & reorders.
        $this->key = $key !== '' ? $key : (string) Str::uuid();
    }
}
