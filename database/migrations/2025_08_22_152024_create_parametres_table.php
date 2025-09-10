<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParametresTable extends Migration
{
    public function up(): void
    {
        Schema::create('parametres', function (Blueprint $table) {
            $table->integer('codeparam')->primary();
            $table->string('typaram', 50);
            $table->string('codtyparam', 50);
            $table->string('libelleparam', 255);
            $table->string('param1')->nullable();
            $table->string('param2')->nullable();
            $table->string('param3')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parametres');
    }
}
