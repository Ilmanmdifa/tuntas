<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Removes the dead `books` learning table (Day 19 Eloquent practice).
     * History kept: the original create-migration stays, cleanup is a new entry.
     */
    public function up(): void
    {
        Schema::dropIfExists('books');
    }

    /**
     * Reverse the migrations.
     *
     * Intentionally empty: rolling back a dead learning table helps nobody.
     */
    public function down(): void
    {
        //
    }
};
