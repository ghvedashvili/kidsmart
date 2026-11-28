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
            'name'  => 'იპოვე ტყუპები',
            'icon'  => '🧠',
            'route' => 'games.memory',
            'type'  => 'score',
        ],
        'quoridor' => [
            'name'  => 'კორიდორი 5×5',
            'icon'  => '🧱',
            'route' => 'games.quoridor',
            'type'  => 'versus',
        ],
        // მათემატიკური თავსატეხები (ერთი გვერდი, სამი რეჟიმი) — 5 რაუნდი, 3 სიცოცხლე
        'math_numbers' => [
            'name'  => 'ჩასვი რიცხვები',
            'icon'  => '🧩',
            'route' => 'games.math-numbers',
            'type'  => 'puzzle',
            'mode'  => 'numbers',
            'desc'  => 'ჩასვი რიცხვები ისე, რომ სწორი ტოლობა გამოვიდეს.',
        ],
        'math_signs' => [
            'name'  => 'ჩასვი მოქმედებები',
            'icon'  => '➕',
            'route' => 'games.math-signs',
            'type'  => 'puzzle',
            'mode'  => 'signs',
            'desc'  => 'ჩასვი მოქმედებები ისე, რომ სწორი ტოლობა გამოვიდეს',
        ],
        'math_chain' => [
            'name'  => 'რიცხვების ქარხანა',
            'icon'  => '🏭',
            'route' => 'games.math-chain',
            'type'  => 'puzzle',
            'mode'  => 'chain',
            'desc'  => 'მოცემული რიცხვი მოქმედებებით გადააკეთე სამიზნე რიცხვამდე',
        ],
        // ციფრის გადატანა: ერთი ციფრი გადაიტანე, რომ ტოლობა სწორი გახდეს (შეუზღუდავი, თანდათან რთულდება)
        'math_move' => [
            'name'  => 'ციფრის გადატანა',
            'icon'  => '↔️',
            'route' => 'games.math-move',
            'type'  => 'puzzle',
            'desc'  => 'გადაიტანე ერთი ციფრი, რომ ტოლობა სწორი გახდეს.',
        ],
        // რიცხვების წვიმა: მაგალითები ცვივა — უპასუხე, სანამ მიწას დაეცემა (3 სიცოცხლე, კომბო ქულები)
        'math_rain' => [
            'name'  => 'რიცხვების წვიმა',
            'icon'  => '🌧️',
            'route' => 'games.math-rain',
            'type'  => 'rain',
            'desc'  => 'დაწერე პასუხი, სანამ მაგალითი მიწამდე ჩამოვა.',
        ],
        // ცხოველების X-O (Tic-Tac-Eat): 🐰 კურდღლები 🦊 მელიების წინააღმდეგ — დიდი ფიგურა პატარაზე ჯდება
        'tic_tac_eat' => [
            'name'  => 'ცხოველების X-O',
            'icon'  => '🦊',
            'route' => 'games.tic-tac-eat',
            'type'  => 'versus',
            'desc'  => 'X-O: კურდღლები მელიების წინააღმდეგ',
        ],
        // კოდის გამტეხი: 22 სახლი × 10 კარი — წაიკითხე პირობები და გააღე საკეტი
        'code_breaker' => [
            'name'  => 'კოდის გამტეხი',
            'icon'  => '🔐',
            'route' => 'games.code-breaker',
            'type'  => 'quest',
            'desc'  => 'წაიკითხე პირობები, გამოიცანი კოდი და გააღე კარები.',
        ],
    ];

    /** კოდის გამტეხის ზომა — უნდა ემთხვეოდეს JS-ის CB.HOUSES / CB.DOORS-ს */
    private const CB_HOUSES = 22;
    private const CB_DOORS  = 10;   // ძველი ვერსია: ყველა სახლში 10 კარი (ბოსი = 9)

    /** კარების რაოდენობა სახლში: 1–6 → 6, 7–14 → 8, 15–22 → 10 (ბოლო კარი ყოველთვის ბოსია) */
    public static function cbDoors(int $h): int
    {
        return $h < 6 ? 6 : ($h < 14 ? 8 : 10);
    }

    /** ძველი პროგრესი (10 კარი ყველგან) → ახალი: ჩვეულებრივი კარები რჩება, ბოსი (9) ახალ ბოლო კარზე გადადის */
    private static function cbMigrate(array $state): array
    {
        if (($state['v'] ?? 1) >= 2) return $state;
        $old = (array) ($state['p'] ?? []);
        $new = [];
        for ($h = 0; $h < self::CB_HOUSES; $h++) {
            $nd = self::cbDoors($h);
            for ($d = 0; $d < $nd - 1; $d++) if (! empty($old["$h-$d"])) $new["$h-$d"] = (int) $old["$h-$d"];
            if (! empty($old["$h-9"])) $new["$h-" . ($nd - 1)] = (int) $old["$h-9"];
        }
        $state['p'] = $new;
        $state['stars'] = array_sum($new);
        $state['v'] = 2;
        return $state;
    }

    /** ადმინმა თამაში გათიშა ან ამ კლასს არ უჩანს → თამაშების გვერდზე */
    private function hiddenRedirect(string $slug)
    {
        $child = auth()->user();
        if (! GameSetting::isVisible($slug, $child->childSetting?->grade_id)) {
            return redirect()->route('games.index')->with('error', 'ეს თამაში ახლა მიუწვდომელია.');
        }
        // ადმინის პარამეტრით: თამაში იხსნება მხოლოდ დღის ტესტის დაწერის შემდეგ
        if (GameSetting::needsTest($slug) && ! self::dailyTestDone($child)) {
            return redirect()->route('games.index')->with('error', 'ჯერ დღის ტესტი დაწერე — მერე ითამაშებ!');
        }
        return null;
    }

    /** დღეს დასრულებული ჩვეულებრივი (არა ოლიმპიადის) ტესტი */
    public static function dailyTestDone($child): bool
    {
        return \App\Models\Test::where('child_id', $child->id)
            ->whereNotNull('completed_at')
            ->whereDate('completed_at', today())
            ->where('is_olympiad', false)
            ->exists();
    }

    /** კოდის გამტეხი: სახლის პირველად დასრულება (ბოსის კარი) — ამდენი მონეტა */
    private const CB_HOUSE_COINS = 10;

    // ── Games hub ────────────────────────────────────────────────────────────
    public function index()
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        // ადმინის პარამეტრები: გათიშული / სხვა კლასის თამაშები არ ჩანს
        $map = GameSetting::map();
        $gradeId = $child->childSetting?->grade_id;
        $testDone = self::dailyTestDone($child);
        $games = collect(self::GAMES)->filter(fn ($info, $slug) => GameSetting::isVisible($slug, $gradeId, $map))->map(function ($info, $slug) use ($child, $map, $testDone) {
            $session = GameSession::forChild($child->id, $slug);
            return array_merge($info, [
                'slug'       => $slug,
                'wins'       => $session->wins,
                'losses'     => $session->losses,
                // წვიმაში ქულა 255-ს აჭარბებს — რეკორდი state.best-შია
                'best_score' => (int) (($session->state['best'] ?? 0) ?: $session->player_score),
                'stars'      => (int) ($session->state['stars'] ?? 0),
                'house'      => (int) ($session->state['house'] ?? 0),
                'locked'     => ! $testDone && GameSetting::needsTest($slug, $map),
            ]);
        });

        $lockedCount = $games->where('locked', true)->count();
        return view('child.games.index', [
            'games'       => $games,
            'locked'      => $lockedCount > 0,
            'allLocked'   => $lockedCount > 0 && $lockedCount === $games->count(),
        ]);
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

    // ── კოდის გამტეხი ───────────────────────────────────────────────────────
    public function codeBreaker()
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);
        if ($r = $this->hiddenRedirect('code_breaker')) return $r;

        $session = GameSession::forChild($child->id, 'code_breaker');
        $state   = (array) ($session->state ?? []);
        if (($state['v'] ?? 1) < 2) {
            $state = self::cbMigrate($state);
            $session->state = $state;
            $session->player_score = min(255, count(array_filter($state['p'])));
            $session->save();
        }
        return view('child.games.code-breaker', [
            'game'     => self::GAMES['code_breaker'],
            'progress' => (object) ($state['p'] ?? []),
            'hints'    => (object) ($state['hint'] ?? []),
        ]);
    }

    /** კარი გაიღო: ვინახავთ ვარსკვლავებს (საუკეთესოს). კარი ღია უნდა იყოს — წინა კარი/სახლი დასრულებული. */
    /** მინიშნება: ერთ კარზე ერთხელ — ინახება, რომ გასვლა/შემოსვლით ახალი არ მიიღოს */
    public function codeBreakerHint(Request $request): JsonResponse
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $data = $request->validate([
            'house' => 'required|integer|min:0|max:' . (self::CB_HOUSES - 1),
            'door'  => 'required|integer|min:0|max:' . (self::CB_DOORS - 1),
            'pos'   => 'required|integer|min:0|max:3',
            'digit' => 'required|integer|min:0|max:9',
            'say'   => 'required|integer|min:0|max:20',
        ]);
        $h = (int) $data['house']; $d = (int) $data['door'];
        if ($d > self::cbDoors($h) - 1) return response()->json(['error' => 'ასეთი კარი ამ სახლში არ არის.'], 422);

        $session = GameSession::forChild($child->id, 'code_breaker');
        $state   = self::cbMigrate((array) ($session->state ?? []));
        $hints   = (array) ($state['hint'] ?? []);
        if (! isset($hints["$h-$d"])) {   // უკვე აღებულს არ ვცვლით
            $hints["$h-$d"] = [(int) $data['pos'], (int) $data['digit'], (int) $data['say']];
            $state['hint'] = $hints;
            $session->state = $state;
            $session->save();
        }
        return response()->json(['hint' => $hints["$h-$d"]]);
    }

    public function codeBreakerSave(Request $request): JsonResponse
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $data = $request->validate([
            'house' => 'required|integer|min:0|max:' . (self::CB_HOUSES - 1),
            'door'  => 'required|integer|min:0|max:' . (self::CB_DOORS - 1),   // ზუსტი ზღვარი ქვემოთ, სახლის მიხედვით
            'stars' => 'required|integer|min:1|max:3',
        ]);
        $h = (int) $data['house']; $d = (int) $data['door'];
        $nd = self::cbDoors($h); $boss = $nd - 1;
        if ($d > $boss) return response()->json(['error' => 'ასეთი კარი ამ სახლში არ არის.'], 422);

        $session = GameSession::forChild($child->id, 'code_breaker');
        $state   = self::cbMigrate((array) ($session->state ?? []));
        $p       = (array) ($state['p'] ?? []);
        $done    = fn ($hh, $dd) => ($p["$hh-$dd"] ?? 0) > 0;
        $open    = ($d === 0 ? ($h === 0 || $done($h - 1, self::cbDoors($h - 1) - 1)) : $done($h, $d - 1));
        if (! $open) return response()->json(['error' => 'ეს კარი ჯერ დაკეტილია.'], 422);

        $isNew = ! $done($h, $d);
        // სახლი პირველად დასრულდა (ბოსის კარი) → მონეტები; განმეორებით გავლაზე აღარ ერიცხება
        $coins = 0;
        if ($isNew && $d === $boss && $child->childSetting) {
            $coins = self::CB_HOUSE_COINS;
            $child->childSetting->increment('coins', $coins);
            $state['coins'] = (int) ($state['coins'] ?? 0) + $coins;
        }
        $p["$h-$d"] = max((int) ($p["$h-$d"] ?? 0), (int) $data['stars']);
        $state['p']     = $p;
        // კარი გაიღო → ამ კარის მინიშნება აღარ გვჭირდება (თავიდან თამაშისას ახლიდან იქნება)
        if (isset($state['hint']["$h-$d"])) unset($state['hint']["$h-$d"]);
        $state['stars'] = array_sum($p);
        $state['house'] = max((int) ($state['house'] ?? 0), $d === $boss ? min(self::CB_HOUSES - 1, $h + 1) : $h);
        $session->state = $state;
        $session->player_score = min(255, count(array_filter($p)));
        $session->save();

        $newAchievements = $isNew ? (new \App\Services\AchievementService())->handleGameFinish($child, 'code_breaker') : [];

        return response()->json([
            'stars'            => $state['stars'],
            'coins'            => $coins,
            'new_achievements' => $newAchievements,
        ]);
    }

    // ── რიცხვების წვიმა ─────────────────────────────────────────────────────
    public function mathMove()
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);
        if ($r = $this->hiddenRedirect('math_move')) return $r;

        $session = GameSession::forChild($child->id, 'math_move');
        return view('child.games.math-move', [
            'game'  => self::GAMES['math_move'],
            'best'  => (int) ($session->state['best'] ?? 0),     // სულ ამოხსნილი (მინიშნების გარეშე)
            'level' => (int) ($session->state['level'] ?? 0),    // რამდენი ამოცანა გაიარა — სირთულე აქედან გრძელდება
        ]);
    }

    public function mathMoveFinish(Request $request): JsonResponse
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        // ყოველი ამოხსნილი ამოცანის შემდეგ: solved = 1 (მინიშნებით — 0), level = გავლილი ამოცანების რაოდენობა
        $data = $request->validate([
            'solved' => 'required|integer|min:0|max:1',
            'level'  => 'required|integer|min:1|max:100000',
        ]);

        $session = GameSession::forChild($child->id, 'math_move');
        $state   = (array) ($session->state ?? []);
        $state['best']  = (int) ($state['best'] ?? 0) + (int) $data['solved'];
        $state['level'] = max((int) ($state['level'] ?? 0), min((int) $data['level'], (int) ($state['level'] ?? 0) + 1));
        $session->state = $state;
        $session->player_score = min(255, $state['best']);
        $session->save();

        $newAchievements = (new \App\Services\AchievementService())->handleGameFinish($child, 'math_move');

        return response()->json(['best' => $state['best'], 'new_achievements' => $newAchievements]);
    }

    public function mathRain()
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);
        if ($r = $this->hiddenRedirect('math_rain')) return $r;

        $session = GameSession::forChild($child->id, 'math_rain');
        $grade   = (int) ($child->childSetting?->grade?->number ?? 2);

        return view('child.games.math-rain', [
            'game'  => self::GAMES['math_rain'],
            'best'  => (int) (($session->state['best'] ?? 0) ?: $session->player_score),
            'grade' => max(1, min(6, $grade)),
        ]);
    }

    public function mathRainFinish(Request $request): JsonResponse
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $data = $request->validate([
            'score'  => 'required|integer|min:0|max:1000000',
            'solved' => 'required|integer|min:0|max:10000',
            'level'  => 'nullable|in:easy,medium,hard',
        ]);

        $session = GameSession::forChild($child->id, 'math_rain');
        $state   = (array) ($session->state ?? []);
        $best    = (int) ($state['best'] ?? 0);
        $isBest  = $data['score'] > $best;
        if ($isBest) $state['best'] = $data['score'];
        $state['games']  = ($state['games'] ?? 0) + 1;
        $state['solved'] = ($state['solved'] ?? 0) + $data['solved'];
        $session->state = $state;
        $session->player_score = min(255, max($session->player_score, $data['solved']));   // tinyint — ამოხსნილი მაგალითების რეკორდი
        $session->save();

        $newAchievements = (new \App\Services\AchievementService())->handleGameFinish($child, 'math_rain');

        return response()->json([
            'best_score'       => (int) ($state['best'] ?? 0),
            'is_best'          => $isBest,
            'new_achievements' => $newAchievements,
        ]);
    }

    // ── ქუორიდორი 5×5 (Quoridor) ────────────────────────────────────────────
    public function ticTacEat()
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);
        if ($r = $this->hiddenRedirect('tic_tac_eat')) return $r;

        return view('child.games.tic-tac-eat', [
            'game'    => self::GAMES['tic_tac_eat'],
            'session' => GameSession::forChild($child->id, 'tic_tac_eat'),
        ]);
    }

    public function ticTacEatFinish(Request $request): JsonResponse
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $data = $request->validate(['result' => 'required|in:win,lose,draw']);

        $session = GameSession::forChild($child->id, 'tic_tac_eat');
        $global  = GameGlobalStat::forGame('tic_tac_eat');
        if ($data['result'] === 'win') {
            $session->wins++;
            $session->current_streak++;
            $global->increment('total_wins');
        } elseif ($data['result'] === 'lose') {
            $session->losses++;
            $session->current_streak = 0;
            $global->increment('total_losses');
        } else {
            $state = (array) ($session->state ?? []);
            $state['draws'] = (int) ($state['draws'] ?? 0) + 1;
            $session->state = $state;
        }
        $session->save();

        $newAchievements = $data['result'] === 'draw' ? [] : (new \App\Services\AchievementService())->handleGameFinish($child, 'tic_tac_eat');

        return response()->json([
            'wins'             => $session->wins,
            'losses'           => $session->losses,
            'new_achievements' => $newAchievements,
        ]);
    }

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
