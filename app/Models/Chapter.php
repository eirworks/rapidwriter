<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    /** @use HasFactory<\Database\Factories\ChapterFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'ordering',
        'story_id',
        'compiled_content',
        'items',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }

    // public function items()
    // {
    //     return $this->hasMany(ChapterItem::class);
    // }
}
