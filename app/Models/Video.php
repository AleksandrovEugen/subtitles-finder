<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Video extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'youtube_id',
        'is_enabled',
        'is_enabled',
        'subtitles',
        'subtitles_autogenerated',
        'playlist_id',
    ];

    public function playlist(): BelongsTo
    {
        return $this->belongsTo(Playlist::class);
    }
}
