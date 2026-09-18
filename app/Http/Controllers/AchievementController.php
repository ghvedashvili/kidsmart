<?php

namespace App\Http\Controllers;

use App\Services\AchievementService;

class AchievementController extends Controller
{
    public function index(AchievementService $service)
    {
        return view('child.achievements', $service->wallFor(auth()->user()));
    }
}
