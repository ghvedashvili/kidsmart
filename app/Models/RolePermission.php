<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    public $timestamps = false;
    protected $fillable = ['role', 'page'];

    const ROLES = ['parent', 'child', 'teacher', 'staff'];

    const PAGES = [
        'dashboard'        => 'დეშბოარდი',
        'tests'            => 'ტესტები',
        'achievements'     => 'მიღწევები',
        'children'         => 'შვილების მართვა',
        // admin sub-pages
        'admin.panel'      => 'Push / შეტყობინებები',
        'admin.grades'     => 'კლასები',
        'admin.themes'     => 'თემატიკა',
        'admin.topics'     => 'თემები',
        'admin.questions'  => 'კითხვები',
        'admin.users'      => 'მომხმარებლები',
        'admin.perms'      => 'ნებართვები',
        'admin.packages'   => 'პაკეტები',
    ];

    const PAGE_ROUTES = [
        'dashboard'        => ['dashboard'],
        'tests'            => ['test.start', 'test.show', 'test.submit', 'test.result'],
        'achievements'     => ['achievements'],
        'children'         => ['child.stats', 'child.test.show', 'child.settings.edit',
                               'child.settings.update', 'child.destroy', 'child.store',
                               'child.link', 'push.remind'],
        'admin.panel'      => ['admin.panel', 'push.send'],
        'admin.grades'     => ['admin.grades.*'],
        'admin.themes'     => ['admin.themes.*'],
        'admin.topics'     => ['admin.topics.*'],
        'admin.questions'  => ['admin.questions.*'],
        'admin.users'      => ['admin.users.*', 'admin.updateRole', 'admin.subscriptions.*'],
        'admin.perms'      => ['admin.permissions.*'],
        'admin.packages'   => ['admin.packages.*'],
    ];

    public static function allowedPages(string $role): ?array
    {
        $rows = static::where('role', $role)->pluck('page')->all();
        return empty($rows) ? null : $rows;
    }

    public static function hasAnyAdminPage(string $role): bool
    {
        return static::where('role', $role)
            ->where('page', 'like', 'admin.%')
            ->exists();
    }

    public static function pageForRoute(string $routeName): ?string
    {
        foreach (self::PAGE_ROUTES as $page => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_ends_with($pattern, '*')) {
                    if (str_starts_with($routeName, rtrim($pattern, '*'))) return $page;
                } elseif ($routeName === $pattern) {
                    return $page;
                }
            }
        }
        return null;
    }
}
