<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * Seeds the achievement catalog. Tier 1 of each pre-existing achievement
     * matches the exact threshold the old hardcoded AchievementService::ACHIEVEMENTS
     * used, so nothing already earned becomes harder or easier retroactively.
     *
     * `context` groups achievements into the category whose activity triggers
     * them (test/practice/olympiad/games), or 'any' for ones checked after
     * every category's activity (day_streak merges test+practice days;
     * coins_earned/difficulty_reached can be crossed by any activity).
     * `game` further scopes a 'games' achievement to one specific game slug
     * (null = applies across all games) — new games just add new rows here,
     * no engine changes needed.
     */
    public function run(): void
    {
        $catalog = [
            // ── Test ──────────────────────────────────────────────────────
            [
                'slug' => 'test_count', 'name' => 'ტესტების რაოდენობა', 'context' => 'test',
                'description' => 'დაწერე {n} ტესტი', 'condition_type' => 'test_count',
                'tiers' => [
                    ['level' => 1, 'label' => 'პირველი ტესტი!', 'threshold' => 1],
                    ['level' => 2, 'label' => 'ტესტების ვარსკვლავი', 'threshold' => 10],
                    ['level' => 3, 'label' => 'ტესტების ჩემპიონი', 'threshold' => 50],
                ],
            ],
            [
                'slug' => 'perfect_score', 'name' => 'იდეალური შედეგი', 'context' => 'test',
                'description' => 'ყველა პასუხი პედრის პასებივით სწორეა', 'condition_type' => 'perfect_score',
                'tiers' => [
                    ['level' => 1, 'label' => 'პედრი!', 'threshold' => null],
                ],
            ],
            [
                'slug' => 'comeback', 'name' => 'დაბრუნება', 'context' => 'test',
                'description' => 'ცუდი ტესტის შემდეგ 80%+ მოიპოვე', 'condition_type' => 'comeback',
                'tiers' => [
                    ['level' => 1, 'label' => 'დაბრუნება!', 'threshold' => null],
                ],
            ],
            [
                'slug' => 'early_bird', 'name' => 'დილის ვარჯიში', 'context' => 'test',
                'description' => 'გააკეთე ტესტი დილის 9:00-მდე', 'condition_type' => 'time_of_day',
                'condition_config' => ['before' => '09:00'],
                'tiers' => [
                    ['level' => 1, 'label' => 'დილა მშვიდობისა', 'threshold' => null],
                ],
            ],
            [
                'slug' => 'night_owl', 'name' => 'საღამოს ვარჯიში', 'context' => 'test',
                'description' => 'გააკეთე ტესტი 22:30-ის შემდეგ', 'condition_type' => 'time_of_day',
                'condition_config' => ['after' => '22:30'],
                'tiers' => [
                    ['level' => 1, 'label' => 'ძილისნებისა', 'threshold' => null],
                ],
            ],
            [
                'slug' => 'speed_seconds', 'name' => 'სისწრაფე', 'context' => 'test',
                'description' => 'ტესტი {n} წამში დაასრულე', 'condition_type' => 'speed_seconds',
                'tiers' => [
                    ['level' => 1, 'label' => 'კრისტიანო რონალდო', 'threshold' => 60],
                    ['level' => 2, 'label' => 'ელვასავით სწრაფი', 'threshold' => 30],
                    ['level' => 3, 'label' => 'შუქის სისწრაფით', 'threshold' => 15],
                ],
            ],
            [
                'slug' => 'consecutive_correct', 'name' => 'ზედიზედ სწორი პასუხები', 'context' => 'test',
                'description' => '{n} კითხვა ზედიზედ სწორად', 'condition_type' => 'consecutive_correct',
                'tiers' => [
                    ['level' => 1, 'label' => 'ლამინე იამალი', 'threshold' => 19],
                    ['level' => 2, 'label' => 'უცდომელი', 'threshold' => 30],
                    ['level' => 3, 'label' => 'სრულყოფილი', 'threshold' => 50],
                ],
            ],
            [
                'slug' => 'max_level_reached', 'name' => 'მაქსიმალური დონე', 'context' => 'test',
                'description' => 'მიაღწიე შენი კლასის მაქსიმალურ დონეს', 'condition_type' => 'max_level_reached',
                'tiers' => [
                    ['level' => 1, 'label' => 'შენი კლასის ჩემპიონი', 'threshold' => null],
                ],
            ],

            // ── Practice ──────────────────────────────────────────────────
            [
                'slug' => 'practice_count', 'name' => 'ვარჯიშის რაოდენობა', 'context' => 'practice',
                'description' => 'უპასუხე {n} ვარჯიშის კითხვას', 'condition_type' => 'practice_count',
                'tiers' => [
                    ['level' => 1, 'label' => 'ვარჯიშის დაწყება', 'threshold' => 50],
                    ['level' => 2, 'label' => 'ვარჯიშის მოყვარული', 'threshold' => 200],
                    ['level' => 3, 'label' => 'ვარჯიშის ოსტატი', 'threshold' => 500],
                ],
            ],
            [
                'slug' => 'practice_consecutive_correct', 'name' => 'ზედიზედ სწორი ვარჯიშში', 'context' => 'practice',
                'description' => '{n} ვარჯიშის კითხვა ზედიზედ სწორად', 'condition_type' => 'practice_consecutive_correct',
                'tiers' => [
                    ['level' => 1, 'label' => 'სწორი სერია', 'threshold' => 15],
                    ['level' => 2, 'label' => 'უცდომელი ვარჯიშში', 'threshold' => 25],
                    ['level' => 3, 'label' => 'სრულყოფილი ვარჯიში', 'threshold' => 40],
                ],
            ],
            [
                'slug' => 'practice_full_rotation', 'name' => 'ყველა თემა', 'context' => 'practice',
                'description' => 'ივარჯიშე ყველა თემაზე', 'condition_type' => 'practice_full_rotation',
                'tiers' => [
                    ['level' => 1, 'label' => 'ყველგან ვიყავი', 'threshold' => null],
                ],
            ],
            [
                'slug' => 'practice_comeback', 'name' => 'დაბრუნება ვარჯიშში', 'context' => 'practice',
                'description' => 'ცუდი სერიის შემდეგ აღდგენა ვარჯიშში', 'condition_type' => 'practice_comeback',
                'tiers' => [
                    ['level' => 1, 'label' => 'ისევ ფეხზე!', 'threshold' => null],
                ],
            ],

            // ── Olympiad ──────────────────────────────────────────────────
            [
                'slug' => 'olympiad_completed', 'name' => 'ოლიმპიადა', 'context' => 'olympiad',
                'description' => 'დაასრულე {n} ოლიმპიადა', 'condition_type' => 'olympiad_completed',
                'tiers' => [
                    ['level' => 1, 'label' => 'ოლიმპიელი', 'threshold' => 1],
                    ['level' => 2, 'label' => 'გამოცდილი ოლიმპიელი', 'threshold' => 3],
                    ['level' => 3, 'label' => 'ოლიმპიური ლეგენდა', 'threshold' => 5],
                ],
            ],
            [
                'slug' => 'olympiad_perfect', 'name' => 'იდეალური ოლიმპიადა', 'context' => 'olympiad',
                'description' => 'ოლიმპიადაში ყველა პასუხი სწორად', 'condition_type' => 'olympiad_perfect',
                'tiers' => [
                    ['level' => 1, 'label' => 'ოლიმპიური ოქრო', 'threshold' => null],
                ],
            ],

            // ── Any (cross-category) ──────────────────────────────────────
            [
                'slug' => 'day_streak', 'name' => 'ზედიზედ დღეები', 'context' => 'any',
                'description' => '{n} დღე ზედიზედ მინიმუმ 1 ტესტი ან ვარჯიში', 'condition_type' => 'day_streak',
                'tiers' => [
                    ['level' => 1, 'label' => 'ლეო მესი', 'threshold' => 10],
                    ['level' => 2, 'label' => 'სერიის ოსტატი', 'threshold' => 20],
                    ['level' => 3, 'label' => 'დაუმარცხებელი', 'threshold' => 30],
                ],
            ],
            [
                'slug' => 'coins_earned', 'name' => 'დაგროვილი მონეტები', 'context' => 'any',
                'description' => 'დააგროვე {n} მონეტა', 'condition_type' => 'coins_earned',
                'tiers' => [
                    ['level' => 1, 'label' => 'დამზოგველი', 'threshold' => 100],
                    ['level' => 2, 'label' => 'მდიდარი', 'threshold' => 500],
                    ['level' => 3, 'label' => 'მილიონერი', 'threshold' => 2000],
                ],
            ],
            [
                'slug' => 'difficulty_reached', 'name' => 'დონის ამაღლება', 'context' => 'any',
                'description' => 'მიაღწიე დონე {n}-ს', 'condition_type' => 'difficulty_reached',
                'tiers' => [
                    ['level' => 1, 'label' => 'მეორე საფეხური', 'threshold' => 2],
                    ['level' => 2, 'label' => 'მესამე საფეხური', 'threshold' => 3],
                    ['level' => 3, 'label' => 'მეოთხე საფეხური', 'threshold' => 4],
                ],
            ],

            // ── Games (game=null applies across every game) ──────────────
            [
                'slug' => 'game_wins', 'name' => 'მოგებები თამაშებში', 'context' => 'games', 'game' => null,
                'description' => 'მოიგე {n}-ჯერ თამაშებში', 'condition_type' => 'game_wins',
                'tiers' => [
                    ['level' => 1, 'label' => 'პირველი გამარჯვება', 'threshold' => 5],
                    ['level' => 2, 'label' => 'გამარჯვებათა სერია', 'threshold' => 20],
                    ['level' => 3, 'label' => 'თამაშების ჩემპიონი', 'threshold' => 50],
                ],
            ],
            [
                'slug' => 'game_win_streak', 'name' => 'ზედიზედ მოგება', 'context' => 'games', 'game' => null,
                'description' => '{n} ზედიზედ მოგება ნებისმიერ თამაშში', 'condition_type' => 'game_win_streak',
                'tiers' => [
                    ['level' => 1, 'label' => 'ცხელი ხელი', 'threshold' => 3],
                    ['level' => 2, 'label' => 'გაუჩერებელი', 'threshold' => 5],
                    ['level' => 3, 'label' => 'დაუმარცხებელი თამაშში', 'threshold' => 10],
                ],
            ],
            [
                'slug' => 'all_games_tried', 'name' => 'ყველა თამაში', 'context' => 'games', 'game' => null,
                'description' => 'ითამაშე ყველა თამაში ერთხელ მაინც', 'condition_type' => 'all_games_tried',
                'tiers' => [
                    ['level' => 1, 'label' => 'მკვლევარი', 'threshold' => null],
                ],
            ],
            // A per-game achievement (e.g. memory's pairs_found/hints_used, once
            // it has something worth gamifying) just adds another row here with
            // its own `game` + a new condition_type in Achievement::CONDITION_TYPES
            // — no engine change needed, same as these three.
        ];

        foreach ($catalog as $def) {
            $tiers = $def['tiers'];
            unset($def['tiers']);
            $def['game'] = $def['game'] ?? null;

            $achievement = Achievement::updateOrCreate(['slug' => $def['slug']], $def);

            foreach ($tiers as $tier) {
                $achievement->tiers()->updateOrCreate(['level' => $tier['level']], $tier);
            }
        }
    }
}
