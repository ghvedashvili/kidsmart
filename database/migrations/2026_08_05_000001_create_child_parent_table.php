<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('child_parent')) {
            Schema::create('child_parent', function (Blueprint $table) {
                $table->unsignedBigInteger('child_id');
                $table->unsignedBigInteger('parent_id');
                $table->timestamps();
                $table->primary(['child_id', 'parent_id']);
                $table->index('child_id');
                $table->index('parent_id');
            });
        }

        // Migrate existing parent_id relationships (skip if already linked)
        DB::table('users')
            ->where('role', 'child')
            ->whereNotNull('parent_id')
            ->get(['id', 'parent_id'])
            ->each(function ($row) {
                DB::table('child_parent')->insertOrIgnore([
                    'child_id'   => $row->id,
                    'parent_id'  => $row->parent_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_parent');
    }
};
