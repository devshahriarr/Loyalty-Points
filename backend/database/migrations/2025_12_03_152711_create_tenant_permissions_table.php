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
        Schema::create('tenant_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade')->onUpdate('cascade');
            $table->enum('program_reward_control', ['active','disable'])->nullable();
            $table->enum('customer_management', ['active','disable'])->nullable();
            $table->enum('edit_global_branding', ['active','disable'])->nullable();
            $table->enum('employee_control', ['active','disable'])->nullable();
            $table->enum('maintenance_mode', ['active','disable'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_permissions');
    }
};
