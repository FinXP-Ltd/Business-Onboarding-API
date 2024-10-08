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
        Schema::table('natural_persons', function(Blueprint $table) {
            $table->text('name_bidx')->nullable(false)->after('name');
            $table->text('mobile_bidx')->nullable(false)->after('mobile');
            $table->text('date_of_birth_bidx')->nullable(false)->after('date_of_birth');
            $table->text('email_address_bidx')->nullable(false)->after('email_address');
        });

        Schema::table('natural_person_addresses', function(Blueprint $table) {
            $table->text('line_1_bidx')->nullable()->after('line_1');
            $table->text('line_2_bidx')->nullable()->after('line_2');
            $table->text('locality_bidx')->nullable()->after('locality');
            $table->text('postal_code_bidx')->nullable()->after('postal_code');
            $table->text('country_bidx')->nullable()->after('country');
            $table->text('nationality_bidx')->nullable()->after('nationality');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('natural_persons', function(Blueprint $table) {
            $table->dropColumn('name_bidx');
            $table->dropColumn('mobile_bidx');
            $table->dropColumn('date_of_birth_bidx');
            $table->dropColumn('email_address_bidx');
        });

        Schema::table('natural_person_addresses', function(Blueprint $table) {
            $table->dropColumn('line_1_bidx');
            $table->dropColumn('line_2_bidx');
            $table->dropColumn('locality_bidx');
            $table->dropColumn('postal_code_bidx');
            $table->dropColumn('country_bidx');
            $table->dropColumn('nationality_bidx');
        });
    }
};
