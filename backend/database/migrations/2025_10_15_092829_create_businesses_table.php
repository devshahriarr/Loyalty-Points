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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->string('name', 100);
            $table->string('slug', 150)->unique();
            $table->string('phone', 20)->nullable();
            // $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('set null')->onUpdate('cascade');
            $table->string('country',255)->nullable();
            $table->string('industry_type')->nullable();
            $table->timestamp('registration_date')->nullable();
            $table->integer('total_branches')->nullable();
            $table->text('branch_locations')->nullable();
            $table->enum('plan_type', ['monthly', 'yearly'])->nullable();
            $table->enum('billing_status', ['active', 'disable'])->nullable();
            $table->string('email', 100)->unique();
            $table->string('logo')->nullable();
            $table->string('address')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
