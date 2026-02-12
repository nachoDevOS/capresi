<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */

    //  :::::::::::::::::::::::::::::::::::::::::::::  NO ELIMINAR LA MIGRACION ::::::::::::::::::::::::::::
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('registerUser_id')->nullable()->constrained('users');
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
}
