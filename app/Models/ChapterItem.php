<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChapterItem extends Model
{
    /** @use HasFactory<\Database\Factories\ChapterItemFactory> */
    use HasFactory;

    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
