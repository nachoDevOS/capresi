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
        Schema::table('salary_purchases', function (Blueprint $table) {
            
            $table->date('date_limit')->nullable();
            $table->foreignId('cashier_id')->nullable()->constrained('cashiers');
            $table->foreignId('delivered_userId')->nullable()->constrained('users');
            $table->string('delivered_userType')->nullable();
          
            $table->dateTime('dateDelivered')->nullable();
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
