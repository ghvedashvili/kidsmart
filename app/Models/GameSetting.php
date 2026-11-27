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
    protected $fillable = ['slug', 'is_active', 'grade_ids', 'needs_test'];

    protected $casts = ['is_active' => 'boolean', 'grade_ids' => 'array', 'needs_test' => 'boolean'];

    /** სპეციალური ჩანაწერი: is_active = „თამაშები იხსნება მხოლოდ დღის ტესტის შემდეგ“ (ზოგადი ჩამრთველი) */
    public const TEST_GATE = '_daily_test_gate';

    /** @return array<string, self> slug => setting */
    public static function map(): array
    {
        if (! Schema::hasTable('game_settings')) return [];
        return static::all()->keyBy('slug')->all();
    }

    /** ზოგადი ჩამრთველი ჩართულია? (ჩანაწერი არ არის → ჩართულია) */
    public static function testGateOn(?array $map = null): bool
    {
        $s = ($map ?? static::map())[self::TEST_GATE] ?? null;
        return $s ? (bool) $s->is_active : true;
    }

    /** ამ თამაშისთვის დღის ტესტი საჭიროა? (ზოგადი ჩამრთველი + თამაშის საკუთარი პარამეტრი) */
    public static function needsTest(string $slug, ?array $map = null): bool
    {
        $map ??= static::map();
        if (! static::testGateOn($map)) return false;
        $s = $map[$slug] ?? null;
        return ! $s || $s->needs_test === null || (bool) $s->needs_test;
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
