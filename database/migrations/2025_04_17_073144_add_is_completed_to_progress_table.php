<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {   
        DB::statement("ALTER TABLE downloading_progress ADD COLUMN is_completed BOOLEAN DEFAULT FALSE");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
