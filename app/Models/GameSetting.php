<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

/**
 * თამაშის ხილვადობა. ჩანაწერი არ არსებობს → თამაში ჩართულია ყველა კლასისთვის.
 * grade_ids ცარიელი/null → ყველა კლასი.
 */
class GameSetting extends Model
{
    protected $fillable = ['slug', 'is_active', 'grade_ids'];

    protected $casts = ['is_active' => 'boolean', 'grade_ids' => 'array'];

    /** @return array<string, self> slug => setting */
    public static function map(): array
    {
        if (! Schema::hasTable('game_settings')) return [];
        return static::all()->keyBy('slug')->all();
    }

    public static function isVisible(string $slug, ?int $gradeId, ?array $map = null): bool
    {
        $s = ($map ?? static::map())[$slug] ?? null;
        if (! $s) return true;
        if (! $s->is_active) return false;
        $grades = array_map('intval', (array) ($s->grade_ids ?? []));
        return ! $grades || ($gradeId !== null && in_array($gradeId, $grades, true));
    }
}
