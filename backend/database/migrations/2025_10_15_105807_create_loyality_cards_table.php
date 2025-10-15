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
        Schema::create('loyality_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id')->unsigned()->nullable();
            $table->foreign('business_id')->references('id')->on('businesses')->onDelete('cascade');
            $table->string('name', 100)->index();
            $table->json('design_json')->nullable();
            $table->string('reward_type', 100);
            $table->integer('reward_threshold')->default(0);
            $table->string('reward_description', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loyality_cards');
    }
};
