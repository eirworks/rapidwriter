<?php

namespace App\Data;

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
    ) {}
}
