<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUserIdToPatients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_entries', function (Blueprint $table) {
            $table->string('doctor_name',200)->default('Doctor Name');
            $table->integer('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_entries', function (Blueprint $table) {
            $table->dropColumn(['doctor_name', 'user_id']);
        });
    }
}
