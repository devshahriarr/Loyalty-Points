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
        Schema::create('customer_analytics', function (Blueprint $table) {
            // $table->id();
            // $table->foreignId('customer_id')->constrained('customers')->onDelete('set null')->onUpdate('cascade');
            // $table->enum('segment', ['regular', 'at-risk', 'churning'])->default('regular');
            // $table->integer('rfm')->default(0);
            // $table->string('reward_points', 255)->nullable();
            // $table->timestamps();

            $table->id();
            $table->unsignedBigInteger('customer_id')->unique()->index();
            $table->unsignedSmallInteger('rfm')->default(0); // 1..5 scale or composite
            $table->enum('segment', ['regular','at-risk','churning'])->default('regular');
            $table->unsignedInteger('visits_count')->default(0);
            $table->decimal('monetary_total', 12, 2)->default(0);
            $table->timestamp('last_visit_at')->nullable();
            $table->unsignedInteger('reward_points')->default(0);
            $table->json('extra')->nullable();
            $table->timestamps();

            // optional FK
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_analytics');
    }
};
