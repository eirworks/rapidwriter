<?php

namespace Database\Seeders;

use App\Models\AiPreset;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AiPresetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AiPreset::factory()->count(10)->create();
    }
}
