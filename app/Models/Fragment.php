<?php

namespace App\Models;

use Carbon\Carbon;
use Elastic\ScoutDriverPlus\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Storage;

/**
 * @property int $id
 * @property string $text
 * @property int $video_id
 * @property string $time_string
 * @property bool $is_autogenerated
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Video $video
 */
class Fragment extends Model
{
    use HasFactory;
    use Searchable;

    protected $appends = [
        'video_image',
    ];

    protected $fillable = [
        'text',
        'video_id',
        'time_string',
        'is_autogenerated',
    ];

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'text' => $this->text,
            'video_id' => $this->video_id,
            'playlist_id' => $this->video->playlist_id,
            'is_autogenerated' => (bool) $this->is_autogenerated,
        ];
    }

    public function getVideoImageAttribute(): string
    {
        return Storage::disk('r2')->url($this->video->attachments);
    }
}
