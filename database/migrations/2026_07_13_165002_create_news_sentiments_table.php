<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('news_sentiments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('news_cache_id')->constrained('news_caches')->onDelete('cascade');
            $table->integer('positive_score')->default(0);
            $table->integer('negative_score')->default(0);
            $table->string('sentiment_label', 20)->nullable(); // Positive, Neutral, Negative
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news_sentiments');
    }
};
