<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    protected $fillable = ['name', 'icon', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function variables(): HasMany
    {
        return $this->hasMany(ThemeVariable::class);
    }

    public function varGroups(): HasMany
    {
        return $this->hasMany(ThemeVarGroup::class);
    }

    // Returns variable_name => [values] — used by form preview JS
    public function variableMap(): array
    {
        $this->loadMissing(['variables.group']);
        $map = [];
        foreach ($this->variables as $var) {
            $values = ($var->group_id && $var->group)
                ? ($var->group->values ?? [])
                : ($var->values ?? []);
            $map[$var->variable_name] = $values;
        }
        return $map;
    }

    // Returns variable_name => single value, unique within groups — used by generate()
    public function resolveVariables(): array
    {
        $this->loadMissing(['variables.group']);
        $result     = [];
        $groupPicks = [];
        $groupIndex = [];

        foreach ($this->variables as $var) {
            if ($var->group_id && $var->group) {
                $gid = $var->group_id;
                if (!isset($groupPicks[$gid])) {
                    $pool = $var->group->values ?? [];
                    shuffle($pool);
                    $groupPicks[$gid] = $pool;
                    $groupIndex[$gid] = 0;
                }
                $idx = $groupIndex[$gid];
                $result[$var->variable_name] = $groupPicks[$gid][$idx] ?? ('?' . $var->variable_name);
                $groupIndex[$gid]++;
            } else {
                $values = $var->values ?? [];
                $result[$var->variable_name] = $values ? $values[array_rand($values)] : '?';
            }
        }

        return $result;
    }
}
