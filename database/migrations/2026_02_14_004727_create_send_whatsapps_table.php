<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('send_whatsapps', function (Blueprint $table) {
            $table->id();
            $table->string('server')->nullable();
            $table->string('session')->nullable();
            $table->string('country_code')->nullable();
            $table->string('phone')->nullable();

            $table->string('url')->nullable();
            $table->text('message')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('send_whatsapps');
    }
};
