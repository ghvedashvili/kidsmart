<?php

namespace App\Http\Controllers;

use App\Services\OlympiadService;
use App\Services\TestGeneratorService;

class OlympiadController extends Controller
{
    public function index(OlympiadService $service)
    {
        return view('child.olympiad', $service->statusFor(auth()->user()));
    }

    public function start(OlympiadService $service, TestGeneratorService $generator)
    {
        $child  = auth()->user();
        $status = $service->statusFor($child);

        if ($status['todays_test']) {
            return redirect()->route('test.show', $status['todays_test']);
        }

        if (! $status['eligible_today']) {
            return redirect()->route('olympiad.index')->with('test_error', $status['reason']);
        }

        $result = $generator->generateOlympiad($child, $status['rule']);

        if (isset($result['error'])) {
            return redirect()->route('olympiad.index')->with('test_error', $result['error']);
        }

        return redirect()->route('test.show', $result['test']);
    }
}
