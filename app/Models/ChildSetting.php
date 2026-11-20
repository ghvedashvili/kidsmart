<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildSetting extends Model
{
    protected $fillable = [
        'user_id', 'grade_id', 'difficulty', 'tests_per_week', 'unlimited_tests', 'coins', 'difficulty_streak',
        'topic_rotation', 'tests_since_level_review', 'practice_topic_rotation', 'template_rotation',
        'practice_template_rotation', 'parent_notify',
    ];

    protected $casts = [
        'topic_rotation'              => 'array',
        'practice_topic_rotation'     => 'array',
        'template_rotation'           => 'array',
        'practice_template_rotation'  => 'array',
        'coins'                       => 'float',
        'unlimited_tests'             => 'boolean',
        'parent_notify'               => 'array',
    ];

    /** მშობლის შეტყობინებების ტიპები (key => სათაური პარამეტრებში) */
    public const PARENT_NOTIFY = [
        'test_done'      => ['ტესტის დასრულება', 'შედეგი, როცა დღის ტესტს დაწერს'],
        'market_request' => ['მარკეტის მოთხოვნა', 'როცა მარკეტში პროდუქტს აირჩევს'],
        'new_medal'      => ['ახალი მედალი', 'როცა ახალ მედალს მოიპოვებს'],
        'level_change'   => ['დონის ცვლილება', 'როცა დონე აიწევს ან შეიცვლება'],
        'olympiad_done'  => ['ოლიმპიადა', 'როცა ოლიმპიადას დაასრულებს'],
    ];

    /** ჩართულია თუ არა მშობლისთვის ეს შეტყობინება (ნაგულისხმევად — ჩართული) */
    public function wantsParentNotify(string $key): bool
    {
        $prefs = $this->parent_notify;
        return ! is_array($prefs) || ! array_key_exists($key, $prefs) || (bool) $prefs[$key];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }
}
