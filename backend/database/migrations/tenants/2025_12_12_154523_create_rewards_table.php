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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();

            // Reward basic info
            $table->string('name');
            $table->string('reward_type'); // Free Item, Cashback, Discount, etc.
            $table->string('earning_rule'); // Per Spend, Per Visit, etc.

            // Reward conditions
            $table->unsignedInteger('threshold'); // e.g. 100 points / 100 tk
            $table->date('start_date')->nullable();
            $table->date('expire_date')->nullable();

            // Reward logo image
            $table->string('logo')->nullable();

            // Status active / inactive
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
