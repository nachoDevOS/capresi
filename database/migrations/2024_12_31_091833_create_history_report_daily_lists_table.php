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
        Schema::create('history_report_daily_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_id')->nullable()->constrained('routes');
            $table->foreignId('agent_id')->nullable()->constrained('users');
            $table->string('agentType')->nullable();
            
            $table->string('type')->nullable();
            $table->dateTime('dateTime')->nullable();


            
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
        Schema::dropIfExists('history_report_daily_lists');
    }
};
