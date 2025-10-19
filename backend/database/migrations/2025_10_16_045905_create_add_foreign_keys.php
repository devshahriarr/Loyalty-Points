<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add foreign keys to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('business_id')
                  ->references('id')
                  ->on('businesses')
                  ->onDelete('set null')
                  ->onUpdate('cascade');

            $table->foreign('branch_id')
                  ->references('id')
                  ->on('branches')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });

        // Add foreign key to businesses table
        Schema::table('businesses', function (Blueprint $table) {
            $table->foreign('owner_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null')
                  ->onUpdate('cascade');
        });

        // Add foreign key to branches table
        Schema::table('branches', function (Blueprint $table) {
            $table->foreign('business_id')
                  ->references('id')
                  ->on('businesses')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop foreign keys from users table
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropForeign(['branch_id']);
        });

        // Drop foreign key from businesses table
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropForeign(['owner_id']);
        });

        // Drop foreign key from branches table
        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
        });
    }
};
