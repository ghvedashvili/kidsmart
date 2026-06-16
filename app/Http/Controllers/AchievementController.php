<?php

namespace App\Http\Controllers;

use App\Models\ChildAchievement;
use App\Services\AchievementService;

class AchievementController extends Controller
{
    public function index()
    {
        $child   = auth()->user();
        $setting = $child->childSetting;

        $earned = ChildAchievement::where('child_id', $child->id)
            ->orderBy('earned_at')
            ->pluck('earned_at', 'slug');

        $totalTests = \App\Models\Test::where('child_id', $child->id)
            ->whereNotNull('completed_at')
            ->count();

        return view('child.achievements', [
            'setting'      => $setting,
            'earned'       => $earned,
            'achievements' => AchievementService::ACHIEVEMENTS,
            'totalTests'   => $totalTests,
        ]);
    }
}
