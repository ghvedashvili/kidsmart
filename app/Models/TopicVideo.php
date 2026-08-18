<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicVideo extends Model
{
    protected $fillable = ['topic_id', 'youtube_id', 'title', 'order'];

    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }

    public static function extractId(string $url): ?string
    {
        $url = trim($url);
        if (preg_match('~youtu\.be/([A-Za-z0-9_\-]{11})~', $url, $m))   return $m[1];
        if (preg_match('~[?&]v=([A-Za-z0-9_\-]{11})~', $url, $m))       return $m[1];
        if (preg_match('~/embed/([A-Za-z0-9_\-]{11})~', $url, $m))       return $m[1];
        if (preg_match('/^[A-Za-z0-9_\-]{11}$/', $url))                   return $url;
        return null;
    }

    public function embedUrl(): string
    {
        return 'https://www.youtube.com/embed/' . $this->youtube_id;
    }

    public function thumbnailUrl(): string
    {
        return 'https://img.youtube.com/vi/' . $this->youtube_id . '/mqdefault.jpg';
    }
}
