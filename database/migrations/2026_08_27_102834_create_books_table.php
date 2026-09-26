<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    /**
     * Run migration — creates `books` table (version control for DB)
     * Pattern: Migration (up = create, down = rollback)
     * Why Schema::create? Laravel's Schema Builder — generates SQL for any DB (SQLite/MySQL/Postgres) without raw SQL
     * Columns: id (PK), title/author/price/stock, timestamps (created_at, updated_at for Eloquent)
     * Avoid: don't use Indonesian column names (judul/harga) — keep English for international standard; `bukus` was wrong plural (Book → books)
     */
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('author')->nullable();
            $table->integer('price');
            $table->integer('stock')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
