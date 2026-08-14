<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            [
                'name'           => 'Free',
                'slug'           => 'free',
                'description'    => 'საბაზო გეგმა — უფასო',
                'price_monthly'  => 0,
                'price_yearly'   => 0,
                'max_children'   => 1,
                'max_difficulty' => 2,
                'is_free'        => true,
                'is_active'      => true,
                'sort_order'     => 0,
            ],
            [
                'name'           => 'Standard',
                'slug'           => 'standard',
                'description'    => 'სტანდარტული გეგმა',
                'price_monthly'  => 9.99,
                'price_yearly'   => 89.99,
                'max_children'   => 3,
                'max_difficulty' => 4,
                'is_free'        => false,
                'is_active'      => true,
                'sort_order'     => 1,
            ],
            [
                'name'           => 'Premium',
                'slug'           => 'premium',
                'description'    => 'პრემიუმ გეგმა — სრული წვდომა',
                'price_monthly'  => 19.99,
                'price_yearly'   => 179.99,
                'max_children'   => 0,
                'max_difficulty' => 5,
                'is_free'        => false,
                'is_active'      => true,
                'sort_order'     => 2,
            ],
        ];

        foreach ($packages as $data) {
            Package::firstOrCreate(['slug' => $data['slug']], $data);
        }
    }
}
