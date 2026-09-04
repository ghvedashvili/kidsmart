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
}
