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
        Schema::create('contract_day_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractDay_id')->nullable()->constrained('contract_days');
            $table->foreignId('attendaceStart_id')->nullable()->constrained('attendances');
            $table->foreignId('attendaceFinish_id')->nullable()->constrained('attendances');
            $table->foreignId('shiftHour_id')->nullable()->constrained('shifts_hours');
            $table->foreignId('hour_id')->nullable()->constrained('hours');


            $table->decimal('amount', 20,6)->nullable();// monto del turno
            $table->integer('minuteLate')->default(0);//para acumular los minutos de atrazos



            $table->time('start')->nullable();//hora de entrada
            $table->time('finish')->nullable();//hora de salida


            $table->smallInteger('lostHour')->default(0);// 1 para indicar que fue perdido o abandono de trabajo 


            $table->double('typeHour', 10,2)->nullable(); //1 es tiempo completo, 0.5 medio tiempo
            
            $table->smallInteger('status')->default(1);

            $table->foreignId('license_id')->nullable()->constrained('licenses');
            $table->string('typeLicense')->nullable();

            
            
            $table->foreignId('register_userId')->nullable()->constrained('users');
            $table->string('register_agentType')->nullable();
            $table->timestamps();

            $table->foreignId('deleted_userId')->nullable()->constrained('users');
            $table->string('deleted_agentType')->nullable();
            $table->text('deleted_observation')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contract_day_attendances');
    }
};
