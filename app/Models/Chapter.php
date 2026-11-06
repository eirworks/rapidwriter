<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    /** @use HasFactory<\Database\Factories\ChapterFactory> */
    use HasFactory;

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    public function items()
    {
        return $this->hasMany(ChapterItem::class);
    }
}
