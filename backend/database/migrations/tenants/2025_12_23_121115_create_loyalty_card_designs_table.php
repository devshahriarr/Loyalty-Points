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
        Schema::create('loyalty_card_designs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loyalty_card_id')->constrained()->cascadeOnDelete();
            $table->integer('stamp_count')->nullable();
            $table->string('logo')->nullable();
            $table->string('background')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('text_color')->nullable();
            $table->string('active_stamp_color')->nullable();
            $table->string('inactive_stamp_color')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyalty_card_designs');
    }
};
