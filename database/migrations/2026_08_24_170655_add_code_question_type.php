<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// question_type is varchar(30), so no enum alteration needed — new value just works
return new class extends Migration {
    public function up(): void {}
    public function down(): void {}
};
