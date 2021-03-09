<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileAssignment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('file_assignments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('file_id');
            $table->integer('patient_id');
            $table->integer('remark_file_id')->default(0);
            $table->string('remarks',5000);
            $table->datetime('deadline');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('file_assignments');
    }
}
