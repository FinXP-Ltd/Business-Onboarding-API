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
        Schema::table('users', function(Blueprint $table) {
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('password')->nullable()->after('last_name');
            $table->string('mobile')->nullable()->after('password');
            $table->string('telephone')->nullable()->after('mobile');
            $table->string('tfa_secret', 20)->nullable()->after('telephone');
            $table->tinyInteger('tfa_enabled')->nullable()->after('tfa_secret');
            $table->tinyInteger('is_active')->nullable()->after('tfa_enabled');
            $table->string('last_ip', 45)->nullable()->after('is_active');
            $table->timestamp('last_logged_in')->nullable()->after('last_ip');
            $table->timestamp('password_updated')->nullable()->after('last_logged_in');
        });

        Schema::table('users', function(Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
           $table->dropColumn('first_name');
           $table->dropColumn('last_name');
           $table->dropColumn('password');
           $table->dropColumn('mobile');
           $table->dropColumn('telephone');
           $table->dropColumn('tfa_secret');
           $table->dropColumn('tfa_enabled');
           $table->dropColumn('is_active');
           $table->dropColumn('last_ip');
           $table->dropColumn('last_logged_in');
           $table->dropColumn('password_updated');
        });

        Schema::create('users', function(Blueprint $table) {
           $table->addColumn('name');
        });
    }
};
