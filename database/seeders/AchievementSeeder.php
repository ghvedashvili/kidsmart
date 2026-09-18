<?php

namespace Database\Seeders;

use App\Models\Achievement;
use Illuminate\Database\Seeder;

class AchievementSeeder extends Seeder
{
    /**
     * Seeds the achievement catalog. Tier 1 of each migrated achievement matches the
     * exact threshold the old hardcoded AchievementService::ACHIEVEMENTS used, so
     * nothing already earned becomes harder or easier retroactively — tiers 2/3 are
     * new, higher milestones layered on top.
     */
    public function run(): void
    {
        $catalog = [
            [
                'slug' => 'test_count', 'name' => 'ტესტების რაოდენობა',
                'description' => 'დაწერე {n} ტესტი', 'condition_type' => 'test_count', 'daily_limit' => true,
                'tiers' => [
                    ['level' => 1, 'label' => 'პირველი ტესტი!', 'threshold' => 1],
                    ['level' => 2, 'label' => 'ტესტების ვარსკვლავი', 'threshold' => 10],
                    ['level' => 3, 'label' => 'ტესტების ჩემპიონი', 'threshold' => 50],
                ],
            ],
            [
                'slug' => 'perfect_score', 'name' => 'იდეალური შედეგი',
                'description' => 'ყველა პასუხი პედრის პასებივით სწორეა', 'condition_type' => 'perfect_score', 'daily_limit' => true,
                'tiers' => [
                    ['level' => 1, 'label' => 'პედრი!', 'threshold' => null],
                ],
            ],
            [
                'slug' => 'comeback', 'name' => 'დაბრუნება',
                'description' => 'ცუდი ტესტის შემდეგ 80%+ მოიპოვე', 'condition_type' => 'comeback', 'daily_limit' => true,
                'tiers' => [
                    ['level' => 1, 'label' => 'დაბრუნება!', 'threshold' => null],
                ],
            ],
            [
                'slug' => 'early_bird', 'name' => 'დილის ვარჯიში',
                'description' => 'გააკეთე ტესტი დილის 9:00-მდე', 'condition_type' => 'time_of_day',
                'condition_config' => ['before' => '09:00'], 'daily_limit' => true,
                'tiers' => [
                    ['level' => 1, 'label' => 'დილა მშვიდობისა', 'threshold' => null],
                ],
            ],
            [
                'slug' => 'night_owl', 'name' => 'საღამოს ვარჯიში',
                'description' => 'გააკეთე ტესტი 22:30-ის შემდეგ', 'condition_type' => 'time_of_day',
                'condition_config' => ['after' => '22:30'], 'daily_limit' => true,
                'tiers' => [
                    ['level' => 1, 'label' => 'ძილისნებისა', 'threshold' => null],
                ],
            ],
            [
                'slug' => 'speed_seconds', 'name' => 'სისწრაფე',
                'description' => 'ტესტი {n} წამში დაასრულე', 'condition_type' => 'speed_seconds', 'daily_limit' => true,
                'tiers' => [
                    ['level' => 1, 'label' => 'კრისტიანო რონალდო', 'threshold' => 60],
                    ['level' => 2, 'label' => 'ელვასავით სწრაფი', 'threshold' => 30],
                    ['level' => 3, 'label' => 'შუქის სისწრაფით', 'threshold' => 15],
                ],
            ],
            [
                'slug' => 'day_streak', 'name' => 'ზედიზედ დღეები',
                'description' => '{n} დღე ზედიზედ მინიმუმ 1 ტესტი', 'condition_type' => 'day_streak', 'daily_limit' => true,
                'tiers' => [
                    ['level' => 1, 'label' => 'ლეო მესი', 'threshold' => 10],
                    ['level' => 2, 'label' => 'სერიის ოსტატი', 'threshold' => 20],
                    ['level' => 3, 'label' => 'დაუმარცხებელი', 'threshold' => 30],
                ],
            ],
            [
                'slug' => 'consecutive_correct', 'name' => 'ზედიზედ სწორი პასუხები',
                'description' => '{n} კითხვა ზედიზედ სწორად', 'condition_type' => 'consecutive_correct', 'daily_limit' => true,
                'tiers' => [
                    ['level' => 1, 'label' => 'ლამინე იამალი', 'threshold' => 19],
                    ['level' => 2, 'label' => 'უცდომელი', 'threshold' => 30],
                    ['level' => 3, 'label' => 'სრულყოფილი', 'threshold' => 50],
                ],
            ],
            [
                'slug' => 'max_level_reached', 'name' => 'მაქსიმალური დონე',
                'description' => 'მიაღწიე შენი კლასის მაქსიმალურ დონეს', 'condition_type' => 'max_level_reached', 'daily_limit' => false,
                'tiers' => [
                    ['level' => 1, 'label' => 'შენი კლასის ჩემპიონი', 'threshold' => null],
                ],
            ],
            [
                'slug' => 'olympiad_completed', 'name' => 'ოლიმპიადა',
                'description' => 'დაასრულე {n} ოლიმპიადა', 'condition_type' => 'olympiad_completed', 'daily_limit' => false,
                'tiers' => [
                    ['level' => 1, 'label' => 'ოლიმპიელი', 'threshold' => 1],
                    ['level' => 2, 'label' => 'გამოცდილი ოლიმპიელი', 'threshold' => 3],
                    ['level' => 3, 'label' => 'ოლიმპიური ლეგენდა', 'threshold' => 5],
                ],
            ],
            [
                'slug' => 'coins_earned', 'name' => 'დაგროვილი მონეტები',
                'description' => 'დააგროვე {n} მონეტა', 'condition_type' => 'coins_earned', 'daily_limit' => false,
                'tiers' => [
                    ['level' => 1, 'label' => 'დამზოგველი', 'threshold' => 100],
                    ['level' => 2, 'label' => 'მდიდარი', 'threshold' => 500],
                    ['level' => 3, 'label' => 'მილიონერი', 'threshold' => 2000],
                ],
            ],
        ];

        foreach ($catalog as $def) {
            $tiers = $def['tiers'];
            unset($def['tiers']);

            $achievement = Achievement::updateOrCreate(['slug' => $def['slug']], $def);

            foreach ($tiers as $tier) {
                $achievement->tiers()->updateOrCreate(['level' => $tier['level']], $tier);
            }
        }
    }
}
