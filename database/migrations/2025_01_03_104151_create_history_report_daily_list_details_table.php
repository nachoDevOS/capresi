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
        Schema::create('history_report_daily_list_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->nullable()->constrained('loans');
            $table->foreignId('historyReport_id')->nullable()->constrained('history_report_daily_lists');

            $table->decimal('dailyPayment', 10, 2)->nullable();
            $table->string('typeLoan')->nullable();
            $table->integer('lateDays')->nullable();
            $table->decimal('latePayment', 10, 2)->nullable();
            $table->string('color')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('history_report_daily_list_details');
    }
};
