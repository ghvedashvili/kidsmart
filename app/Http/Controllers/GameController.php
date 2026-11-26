<?php

namespace App\Http\Controllers;

use App\Models\GameGlobalStat;
use App\Models\GameSession;
use App\Models\GameSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public const GAMES = [
        'rock_paper_scissors' => [
            'name'  => 'ქვა-ქაღალდი-მაკრატელი',
            'icon'  => '✊',
            'route' => 'games.rps',
            'type'  => 'versus',
        ],
        'wolves_and_hare' => [
            'name'  => 'ბაჭია და მგლები',
            'icon'  => '🐰',
            'route' => 'games.wolves-hare',
            'type'  => 'versus',
        ],
        'memory' => [
            'name'  => 'მეხსიერების თამაში',
            'icon'  => '🧠',
            'route' => 'games.memory',
            'type'  => 'score',
        ],
        'quoridor' => [
            'name'  => 'ქუორიდორი 5×5',
            'icon'  => '🧱',
            'route' => 'games.quoridor',
            'type'  => 'versus',
        ],
        // მათემატიკური თავსატეხები (ერთი გვერდი, სამი რეჟიმი) — 5 რაუნდი, 3 სიცოცხლე
        'math_numbers' => [
            'name'  => 'იპოვე რიცხვები',
            'icon'  => '🧩',
            'route' => 'games.math-numbers',
            'type'  => 'puzzle',
            'mode'  => 'numbers',
            'desc'  => 'ჩასვი რიცხვები ისე, რომ სამიზნე გამოვიდეს.',
        ],
        'math_signs' => [
            'name'  => 'დასვი ნიშანი',
            'icon'  => '➕',
            'route' => 'games.math-signs',
            'type'  => 'puzzle',
            'mode'  => 'signs',
            'desc'  => 'რიცხვებს შორის სწორი ნიშნები დასვი.',
        ],
        'math_chain' => [
            'name'  => 'რიცხვების ჯაჭვი',
            'icon'  => '🔗',
            'route' => 'games.math-chain',
            'type'  => 'puzzle',
            'mode'  => 'chain',
            'desc'  => 'ნაბიჯ-ნაბიჯ მიიყვანე რიცხვი სამიზნემდე.',
        ],
    ];

    /** ადმინმა თამაში გათიშა ან ამ კლასს არ უჩანს → თამაშების გვერდზე */
    private function hiddenRedirect(string $slug)
    {
        $child = auth()->user();
        if (GameSetting::isVisible($slug, $child->childSetting?->grade_id)) return null;
        return redirect()->route('games.index')->with('error', 'ეს თამაში ახლა მიუწვდომელია.');
    }

    // ── Games hub ────────────────────────────────────────────────────────────
    public function index()
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        // ადმინის პარამეტრები: გათიშული / სხვა კლასის თამაშები არ ჩანს
        $map = GameSetting::map();
        $gradeId = $child->childSetting?->grade_id;
        $games = collect(self::GAMES)->filter(fn ($info, $slug) => GameSetting::isVisible($slug, $gradeId, $map))->map(function ($info, $slug) use ($child) {
            $session = GameSession::forChild($child->id, $slug);
            return array_merge($info, [
                'slug'       => $slug,
                'wins'       => $session->wins,
                'losses'     => $session->losses,
                'best_score' => $session->player_score,
            ]);
        });

        return view('child.games.index', ['games' => $games]);
    }

    // ── Rock-Paper-Scissors ─────────────────────────────────────────────────
    public function rps()
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);
        if ($r = $this->hiddenRedirect('rock_paper_scissors')) return $r;

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
        $newAchievements = [];

        if ($session->player_score >= 3 || $session->computer_score >= 3) {
            $matchOver     = true;
            $finalPlayer   = $session->player_score;
            $finalComputer = $session->computer_score;
            $won           = $session->player_score > $session->computer_score;
            if ($won) {
                $session->wins++;
                $session->current_streak++;
                $global->increment('total_wins');
            } else {
                $session->losses++;
                $session->current_streak = 0;
                $global->increment('total_losses');
            }
            $session->player_score   = 0;
            $session->computer_score = 0;
            $newAchievements = (new \App\Services\AchievementService())->handleGameFinish($child, 'rock_paper_scissors');
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
            'new_achievements'     => $newAchievements,
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
        if ($r = $this->hiddenRedirect('wolves_and_hare')) return $r;

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
        $won     = $data['result'] === 'win';

        if ($won) {
            $session->wins++;
            $session->current_streak++;
            $global->increment('total_wins');
        } else {
            $session->losses++;
            $session->current_streak = 0;
            $global->increment('total_losses');
        }
        $session->state = null;
        $session->save();
        $global->refresh();

        $newAchievements = (new \App\Services\AchievementService())->handleGameFinish($child, 'wolves_and_hare');

        return response()->json([
            'wins'             => $session->wins,
            'losses'           => $session->losses,
            'new_achievements' => $newAchievements,
            'global'           => [
                'total_wins'   => $global->total_wins,
                'total_losses' => $global->total_losses,
            ],
        ]);
    }

    // ── მეხსიერების თამაში (Memory / find the pairs) ───────────────────────
    public function memory()
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);
        if ($r = $this->hiddenRedirect('memory')) return $r;

        $session = GameSession::forChild($child->id, 'memory');

        return view('child.games.memory', [
            'session' => $session,
            'child'   => $child,
        ]);
    }

    public function memoryFinish(Request $request): JsonResponse
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $data = $request->validate([
            'pairs_found' => 'required|integer|min:0',
            'hints_used'  => 'nullable|integer|min:0',
        ]);

        $session  = GameSession::forChild($child->id, 'memory');
        $isBest   = $data['pairs_found'] > $session->player_score;

        if ($isBest) {
            $session->player_score = $data['pairs_found'];
            $session->state        = ['hints_used' => $data['hints_used'] ?? 0];
            $session->save();
        }

        $rank = GameSession::where('game', 'memory')
            ->where('player_score', '>', $session->player_score)
            ->count() + 1;

        // Memory has no win/lose, so only all_games_tried (and any other
        // game-scoped=null achievement) is reachable from here — game_wins/
        // game_win_streak stay untouched since this never updates wins/losses.
        $newAchievements = (new \App\Services\AchievementService())->handleGameFinish($child, 'memory');

        return response()->json([
            'best_score'       => $session->player_score,
            'is_best'          => $isBest,
            'rank'             => $rank,
            'new_achievements' => $newAchievements,
        ]);
    }

    public function memoryLeaderboard(): JsonResponse
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $top = GameSession::where('game', 'memory')
            ->where('player_score', '>', 0)
            ->with('child:id,name')
            ->orderByDesc('player_score')
            ->orderBy('updated_at')
            ->limit(10)
            ->get()
            ->map(fn ($s) => [
                'name'        => $s->child?->name ?? 'ბავშვი',
                'score'       => $s->player_score,
                'hints_used'  => $s->state['hints_used'] ?? 0,
                'is_me'       => $s->child_id === $child->id,
            ]);

        return response()->json(['leaderboard' => $top]);
    }

    // ── მათემატიკური თავსატეხები: იპოვე რიცხვები / დასვი ნიშანი / რიცხვების ჯაჭვი ──
    public function mathNumbers() { return $this->mathGame('math_numbers'); }
    public function mathSigns()   { return $this->mathGame('math_signs'); }
    public function mathChain()   { return $this->mathGame('math_chain'); }

    private function mathGame(string $slug)
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);
        if ($r = $this->hiddenRedirect($slug)) return $r;

        $session = GameSession::forChild($child->id, $slug);
        // სირთულე ბავშვის კლასზეა მორგებული (II–V)
        $grade = (int) ($child->childSetting?->grade?->number ?? 2);

        return view('child.games.math-puzzle', [
            'slug'    => $slug,
            'game'    => self::GAMES[$slug],
            'session' => $session,
            'grade'   => max(1, min(6, $grade)),
            'others'  => collect(self::GAMES)->filter(fn ($g) => ($g['type'] ?? '') === 'puzzle')->map(fn ($g, $s) => ['name' => $g['name'], 'icon' => $g['icon'], 'route' => $g['route'], 'slug' => $s]),
        ]);
    }

    public function mathFinish(Request $request): JsonResponse
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $data = $request->validate([
            'game'  => 'required|in:math_numbers,math_signs,math_chain',
            'score' => 'required|integer|min:0|max:10000',
            'time'  => 'nullable|integer|min:0|max:86400',
        ]);

        // თამაში სიცოცხლის ამოწურვამდე გრძელდება: player_score — რეკორდი (ამოხსნილი თავსატეხები)
        $session = GameSession::forChild($child->id, $data['game']);
        $state   = (array) ($session->state ?? []);
        $isBest  = $data['score'] > $session->player_score;
        if ($isBest) $session->player_score = $data['score'];
        $state['games'] = ($state['games'] ?? 0) + 1;
        $state['total'] = ($state['total'] ?? 0) + $data['score'];
        $session->state = $state;
        $session->save();

        $newAchievements = (new \App\Services\AchievementService())->handleGameFinish($child, $data['game']);

        return response()->json([
            'best_score'       => $session->player_score,
            'is_best'          => $isBest,
            'new_achievements' => $newAchievements,
        ]);
    }

    // ── ქუორიდორი 5×5 (Quoridor) ────────────────────────────────────────────
    public function quoridor()
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);
        if ($r = $this->hiddenRedirect('quoridor')) return $r;

        $session = GameSession::forChild($child->id, 'quoridor');
        $global  = GameGlobalStat::forGame('quoridor');

        return view('child.games.quoridor', compact('session', 'global'));
    }

    public function quoridorState(Request $request): JsonResponse
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $data = $request->validate([
            'state' => 'required|array',
        ]);

        $session = GameSession::forChild($child->id, 'quoridor');
        $session->state = $data['state'];
        $session->save();

        return response()->json(['ok' => true]);
    }

    public function quoridorFinish(Request $request): JsonResponse
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $data = $request->validate([
            'result' => 'required|in:win,lose',
        ]);

        $session = GameSession::forChild($child->id, 'quoridor');
        $global  = GameGlobalStat::forGame('quoridor');
        $won     = $data['result'] === 'win';

        if ($won) {
            $session->wins++;
            $session->current_streak++;
            $global->increment('total_wins');
        } else {
            $session->losses++;
            $session->current_streak = 0;
            $global->increment('total_losses');
        }
        $session->state = null;
        $session->save();
        $global->refresh();

        $newAchievements = (new \App\Services\AchievementService())->handleGameFinish($child, 'quoridor');

        return response()->json([
            'wins'             => $session->wins,
            'losses'           => $session->losses,
            'new_achievements' => $newAchievements,
            'global'           => [
                'total_wins'   => $global->total_wins,
                'total_losses' => $global->total_losses,
            ],
        ]);
    }
}
