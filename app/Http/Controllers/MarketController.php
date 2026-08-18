<?php

namespace App\Http\Controllers;

use App\Models\MarketItem;
use App\Models\MarketPurchase;
use App\Models\User;
use Illuminate\Http\Request;

class MarketController extends Controller
{
    // All template definitions — hardcoded, no DB needed
    public static function templates(): array
    {
        return [
            'გასეირნება' => [
                ['icon' => '🦁', 'title' => 'ზოოპარკი',              'cost' => 50],
                ['icon' => '🎡', 'title' => 'მთაწმინდის პარკი',      'cost' => 60],
                ['icon' => '🍔', 'title' => 'მაკდონალდსი',            'cost' => 40],
                ['icon' => '🎬', 'title' => 'კინო',                   'cost' => 45],
                ['icon' => '🎳', 'title' => 'ბოულინგი',               'cost' => 50],
                ['icon' => '🏊', 'title' => 'აუზი',                   'cost' => 35],
                ['icon' => '🎠', 'title' => 'ატრაქციონები',           'cost' => 40],
                ['icon' => '⛸️', 'title' => 'კონკი',                  'cost' => 45],
                ['icon' => '🏖️', 'title' => 'ზღვა',                  'cost' => 70],
            ],
            'საჩუქარი' => [
                ['icon' => '🍕', 'title' => 'პიცა სახლში',           'cost' => 30],
                ['icon' => '🍦', 'title' => 'ნაყინი',                 'cost' => 15],
                ['icon' => '📚', 'title' => 'წიგნი',                  'cost' => 25],
                ['icon' => '🎁', 'title' => 'სიურპრიზი',              'cost' => 50],
                ['icon' => '🧁', 'title' => 'ტორტი',                  'cost' => 35],
                ['icon' => '🍫', 'title' => 'შოკოლადი',               'cost' => 10],
                ['icon' => '🍬', 'title' => 'ტკბილეული',              'cost' => 10],
                ['icon' => '🎂', 'title' => 'დაბადების დღის ტორტი',   'cost' => 60],
            ],
            'ტანსაცმელი' => [
                ['icon' => '👕', 'title' => 'მაიკა',                  'cost' => 60],
                ['icon' => '👟', 'title' => 'სნიკერსი',               'cost' => 100],
                ['icon' => '🧢', 'title' => 'კეპი',                   'cost' => 50],
                ['icon' => '🎒', 'title' => 'ჩანთა',                  'cost' => 80],
                ['icon' => '🧦', 'title' => 'წინდები',                'cost' => 20],
                ['icon' => '🧣', 'title' => 'შარფი',                  'cost' => 30],
                ['icon' => '🕶️', 'title' => 'სათვალე',               'cost' => 40],
            ],
            'გართობა' => [
                ['icon' => '🎮', 'title' => 'ვიდეო თამაში (1 სთ)',    'cost' => 20],
                ['icon' => '🧩', 'title' => 'პაზლი',                  'cost' => 40],
                ['icon' => '🎨', 'title' => 'ფერადი ფანქრები',        'cost' => 30],
                ['icon' => '⚽', 'title' => 'ბურთი',                  'cost' => 50],
                ['icon' => '🎯', 'title' => 'სათამაშო',               'cost' => 35],
                ['icon' => '🛴', 'title' => 'სკუტერი',                'cost' => 120],
                ['icon' => '🚲', 'title' => 'ველოსიპედი',             'cost' => 150],
            ],
            'მშობელთან დრო' => [
                ['icon' => '🎲', 'title' => 'სამაგიდო თამაში',        'cost' => 20],
                ['icon' => '🎥', 'title' => 'ფილმის ღამე',            'cost' => 25],
                ['icon' => '🍳', 'title' => 'ერთად სამზარეულო',       'cost' => 20],
                ['icon' => '🚴', 'title' => 'ველოსიპედით გასეირნება', 'cost' => 30],
                ['icon' => '🌳', 'title' => 'პიკნიკი',                'cost' => 35],
                ['icon' => '🛁', 'title' => 'ბუშტების აბაზანა',       'cost' => 15],
            ],
        ];
    }

    // ── Child: view own market ─────────────────────────────────────
    public function childIndex()
    {
        $child = auth()->user();
        abort_if($child->role !== 'child', 403);

        $items = MarketItem::where('child_id', $child->id)
            ->where('is_active', true)
            ->orderBy('category')
            ->orderBy('coin_cost')
            ->get();

        $approved = MarketPurchase::where('child_id', $child->id)
            ->where('status', 'approved')
            ->with('item')
            ->latest()
            ->get();

        return view('child.market', [
            'items'    => $items,
            'approved' => $approved,
            'coins'    => $child->childSetting?->coins ?? 0,
        ]);
    }

    // ── Parent: manage child's market ──────────────────────────────
    public function index(User $child)
    {
        $this->authorizeParent($child);

        $items    = MarketItem::where('child_id', $child->id)->orderBy('created_at')->get();
        $pending  = MarketPurchase::where('child_id', $child->id)
            ->where('status', 'pending')
            ->with('item')
            ->latest()
            ->get();
        $approved = MarketPurchase::where('child_id', $child->id)
            ->where('status', 'approved')
            ->with('item')
            ->latest()
            ->take(20)
            ->get();

        return view('market.index', [
            'child'     => $child,
            'items'     => $items,
            'pending'   => $pending,
            'approved'  => $approved,
            'templates' => self::templates(),
        ]);
    }

    public function store(Request $request, User $child)
    {
        $this->authorizeParent($child);

        $data = $request->validate([
            'title'     => 'required|string|max:80',
            'icon'      => 'required|string|max:10',
            'category'  => 'nullable|string|max:50',
            'coin_cost' => 'required|integer|min:1|max:9999',
        ]);

        MarketItem::create(array_merge($data, ['child_id' => $child->id]));

        return back()->with('success', $data['title'] . ' დაემატა მარკეტში');
    }

    public function destroy(MarketItem $item)
    {
        $this->authorizeParent($item->child);
        $item->delete();
        return back()->with('success', 'წაიშალა');
    }

    public function toggleItem(MarketItem $item)
    {
        $this->authorizeParent($item->child);
        $item->update(['is_active' => ! $item->is_active]);
        return back();
    }

    // ── Child: buy item ────────────────────────────────────────────
    public function buy(MarketItem $item)
    {
        $child   = auth()->user();
        $setting = $child->childSetting;

        abort_if($item->child_id !== $child->id, 403);
        abort_if(! $item->is_active, 403);

        // Prevent duplicate pending purchase
        if ($item->pendingPurchase($child->id)) {
            return back()->with('market_error', 'ეს მოთხოვნა უკვე ელოდება მშობლის პასუხს');
        }

        if (($setting->coins ?? 0) < $item->coin_cost) {
            return back()->with('market_error', 'საკმარისი მონეტა არ გაქვს');
        }

        $setting->decrement('coins', $item->coin_cost);

        MarketPurchase::create([
            'market_item_id' => $item->id,
            'child_id'       => $child->id,
            'status'         => 'pending',
            'coins_spent'    => $item->coin_cost,
        ]);

        return back()->with('market_ok', $item->icon . ' ' . $item->title . ' — მოთხოვნა გაიგზავნა!');
    }

    // ── Parent: approve ────────────────────────────────────────────
    public function approve(MarketPurchase $purchase)
    {
        $this->authorizeParent($purchase->child);
        abort_if($purchase->status !== 'pending', 422);

        $purchase->update(['status' => 'approved']);

        return back()->with('success', $purchase->item->icon . ' ' . $purchase->item->title . ' — დადასტურდა!');
    }

    // ── Parent: cancel (refund coins) ──────────────────────────────
    public function cancel(MarketPurchase $purchase)
    {
        $this->authorizeParent($purchase->child);
        abort_if($purchase->status !== 'pending', 422);

        $purchase->child->childSetting?->increment('coins', $purchase->coins_spent);
        $purchase->update(['status' => 'cancelled']);

        return back()->with('success', 'გაუქმდა · ' . $purchase->coins_spent . ' მონეტა დაბრუნდა');
    }

    // ── Helpers ───────────────────────────────────────────────────
    private function authorizeParent(User $child): void
    {
        $parent = auth()->user();
        abort_if(
            ! in_array($parent->role, ['parent', 'admin']) ||
            (! $parent->children()->where('users.id', $child->id)->exists() && $parent->role !== 'admin'),
            403
        );
    }
}
