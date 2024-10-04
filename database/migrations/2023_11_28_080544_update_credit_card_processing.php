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
        Schema::table('company_credit_card_processing', function (Blueprint $table) {
            $table->string('average_ticket_amount')->change();
            $table->string('highest_ticket_amount')->change();
            $table->string('cb_volumes_twelve_months')->change();
            $table->string('refund_twelve_months')->change();
            $table->string('sales_volumes_twelve_months')->change();
        });
    }

    public function down()
    {
        Schema::table('company_credit_card_processing', function (Blueprint $table) {
            $table->double('average_ticket_amount')->change();
            $table->double('highest_ticket_amount')->change();
            $table->double('cb_volumes_twelve_months')->change();
            $table->double('refund_twelve_months')->change();
            $table->double('sales_volumes_twelve_months')->change();
        });
    }
};
