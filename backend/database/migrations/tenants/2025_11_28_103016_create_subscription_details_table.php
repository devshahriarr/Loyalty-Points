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
        Schema::create('subscription_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade')->onUpdate('cascade');
            $table->string('feature', 255);
            $table->integer('location_count')->nullable();
            $table->integer('card_count')->nullable();
            $table->enum('card_type', ['points-card', 'stamps-card', 'reward-card', 'membership-card'])->nullable();
            $table->enum('plan_status', ['active', 'disable'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_details');
    }
};
