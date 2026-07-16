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
        Schema::create('country_economic_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->decimal('gdp_billions', 15, 2)->nullable();
            $table->decimal('inflation_rate', 5, 2)->nullable();
            $table->bigInteger('population')->nullable();
            $table->decimal('exports_billions', 15, 2)->nullable();
            $table->decimal('imports_billions', 15, 2)->nullable();
            $table->timestamps();
            
            $table->unique(['country_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('country_economic_data');
    }
};
