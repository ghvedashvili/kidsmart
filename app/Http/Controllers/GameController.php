<?php

namespace App\Http\Controllers;

use App\Models\GameGlobalStat;
use App\Models\GameSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    private const GAMES = [
        'rock_paper_scissors' => [
            'name'  => 'ქვა-ქაღალდი-მაკრატელი',
            'icon'  => '✊',
            'route' => 'games.rps',
        ],
        'wolves_and_hare' => [
            'name'  => 'ბაჭია და მგლები',
            'icon'  => '🐰',
            'route' => 'games.wolves-hare',
        ],
    ];

    // ── Games hub ────────────────────────────────────────────────────────────
    public function index()
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $games = collect(self::GAMES)->map(function ($info, $slug) use ($child) {
            $session = GameSession::forChild($child->id, $slug);
            return array_merge($info, [
                'slug'   => $slug,
                'wins'   => $session->wins,
                'losses' => $session->losses,
            ]);
        });

        return view('child.games.index', ['games' => $games]);
    }

    // ── Rock-Paper-Scissors ─────────────────────────────────────────────────
    public function rps()
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $session = GameSession::forChild($child->id, 'rock_paper_scissors');
        $global  = GameGlobalStat::forGame('rock_paper_scissors');

        return view('child.games.rock-paper-scissors', compact('session', 'global'));
    }

    public function round(Request $request): JsonResponse
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $data = $request->validate([
            'result' => 'required|in:win,lose,tie',
        ]);

        $session = GameSession::forChild($child->id, 'rock_paper_scissors');

        if ($data['result'] === 'win') {
            $session->player_score++;
        } elseif ($data['result'] === 'lose') {
            $session->computer_score++;
        }

        $matchOver = false;
        $won       = null;
        $finalPlayer   = null;
        $finalComputer = null;
        $global    = GameGlobalStat::forGame('rock_paper_scissors');

        if ($session->player_score >= 3 || $session->computer_score >= 3) {
            $matchOver     = true;
            $finalPlayer   = $session->player_score;
            $finalComputer = $session->computer_score;
            $won           = $session->player_score > $session->computer_score;
            if ($won) {
                $session->wins++;
                $global->increment('total_wins');
            } else {
                $session->losses++;
                $global->increment('total_losses');
            }
            $session->player_score   = 0;
            $session->computer_score = 0;
        }

        $session->save();
        $global->refresh();

        return response()->json([
            'player_score'         => $session->player_score,
            'computer_score'       => $session->computer_score,
            'wins'                 => $session->wins,
            'losses'               => $session->losses,
            'match_over'           => $matchOver,
            'won'                  => $won,
            'final_player_score'   => $finalPlayer,
            'final_computer_score' => $finalComputer,
            'global'               => [
                'total_wins'   => $global->total_wins,
                'total_losses' => $global->total_losses,
            ],
        ]);
    }

    // ── ბაჭია და მგლები (Wolves & Hare) ─────────────────────────────────────
    public function wolvesHare()
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $session = GameSession::forChild($child->id, 'wolves_and_hare');
        $global  = GameGlobalStat::forGame('wolves_and_hare');

        return view('child.games.wolves-hare', compact('session', 'global'));
    }

    public function wolvesHareState(Request $request): JsonResponse
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $data = $request->validate([
            'state' => 'required|array',
        ]);

        $session = GameSession::forChild($child->id, 'wolves_and_hare');
        $session->state = $data['state'];
        $session->save();

        return response()->json(['ok' => true]);
    }

    public function wolvesHareFinish(Request $request): JsonResponse
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $data = $request->validate([
            'result' => 'required|in:win,lose',
        ]);

        $session = GameSession::forChild($child->id, 'wolves_and_hare');
        $global  = GameGlobalStat::forGame('wolves_and_hare');

        if ($data['result'] === 'win') {
            $session->wins++;
            $global->increment('total_wins');
        } else {
            $session->losses++;
            $global->increment('total_losses');
        }
        $session->state = null;
        $session->save();
        $global->refresh();

        return response()->json([
            'wins'   => $session->wins,
            'losses' => $session->losses,
            'global' => [
                'total_wins'   => $global->total_wins,
                'total_losses' => $global->total_losses,
            ],
        ]);
    }
}
