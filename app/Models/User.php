<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'role',
        'parent_code',
        'parent_id',
        'child_code',
        'is_active',
        'avatar',
        'avatar_img',
        'photo_url',
        'first_login_at',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function children()
    {
        return $this->belongsToMany(User::class, 'child_parent', 'parent_id', 'child_id')
                    ->with(['childSetting.grade', 'themes', 'topics']);
    }

    public function parents()
    {
        return $this->belongsToMany(User::class, 'child_parent', 'child_id', 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function childSetting()
    {
        return $this->hasOne(ChildSetting::class);
    }

    public function themes()
    {
        return $this->belongsToMany(Theme::class, 'child_theme');
    }

    public function topics()
    {
        return $this->belongsToMany(Topic::class, 'child_topic');
    }

    public function tests()
    {
        return $this->hasMany(Test::class, 'child_id');
    }

    public function achievements()
    {
        return $this->hasMany(ChildAchievement::class, 'child_id');
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function activeSubscription(): ?UserSubscription
    {
        return $this->subscriptions()
            ->with('package')
            ->latest()
            ->get()
            ->first(fn($s) => $s->isActive());
    }

    public function currentPackage(): Package
    {
        $sub = $this->activeSubscription();
        if ($sub) return $sub->package;

        return Package::where('is_free', true)->first()
            ?? new Package(['name' => 'Free', 'max_children' => 1, 'max_difficulty' => 2, 'is_free' => true]);
    }

    public function syncChildActivation(): void
    {
        $limit    = $this->currentPackage()->max_children;
        $children = $this->belongsToMany(User::class, 'child_parent', 'parent_id', 'child_id')
                         ->withTimestamps()
                         ->orderByPivot('created_at', 'asc')
                         ->get();

        foreach ($children as $i => $child) {
            $shouldBeActive = $limit === 0 || ($i + 1) <= (int) $limit;
            if ((bool) $child->is_active !== $shouldBeActive) {
                $child->update(['is_active' => $shouldBeActive]);
            }
        }
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
        'first_login_at'    => 'datetime',
    ];

    /** ბავშვის პროფილის სურათების სია სქესის მიხედვით: ['boys/b01', ...] */
    public static function avatarChoices(?string $gender): array
    {
        $dir = $gender === 'girl' ? 'girls' : ($gender === 'boy' ? 'boys' : null);
        if (! $dir) return [];
        return collect(glob(public_path("avatars/{$dir}/*.webp")) ?: [])
            ->map(fn ($f) => $dir . '/' . pathinfo($f, PATHINFO_FILENAME))
            ->sort()->values()->all();
    }

    /**
     * პროფილის სურათის გასაღები: არჩეული, ან — თუ არ აურჩევიათ — სქესის მიხედვით
     * „შემთხვევითი“, მაგრამ მუდმივი (ბავშვის id-ით), რომ ყოველ ჩატვირთვაზე არ შეიცვალოს.
     */
    public function avatarKey(): ?string
    {
        $img = $this->avatar_img;
        if ($img && preg_match('#^(boys/b|girls/g)\d{2}$#', $img)) return $img;
        $pool = in_array($this->avatar, ['boy', 'girl'], true)
            ? self::avatarChoices($this->avatar)
            : array_merge(self::avatarChoices('boy'), self::avatarChoices('girl'));
        if (! $pool) return null;
        return $pool[abs(crc32('ks-av-' . $this->id)) % count($pool)];
    }

    /** პროფილის სურათის URL (ყოველთვის სურათი, თუ public/avatars-ში ფაილებია) */
    public function avatarUrl(): ?string
    {
        $key = $this->avatarKey();
        return $key ? asset("avatars/{$key}.webp") : null;
    }
}
