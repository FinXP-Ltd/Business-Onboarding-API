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
        Schema::rename('shareholders', 'business_compositionable');
        Schema::table('business_compositionable', function (Blueprint $table) {
            $table->renameColumn('shareholdable_id', 'business_compositionable_id');
            $table->renameColumn('shareholdable_type', 'business_compositionable_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('business_compositionable', 'shareholders');
        Schema::table('business_compositionable', function (Blueprint $table) {
            $table->renameColumn('business_compositionable_id', 'shareholdable_id');
            $table->renameColumn('business_compositionable_type', 'shareholdable_type');
        });
    }
};
