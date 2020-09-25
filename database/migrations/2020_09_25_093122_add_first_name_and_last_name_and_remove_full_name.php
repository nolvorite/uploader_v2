<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFirstNameAndLastNameAndRemoveFullName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_entries', function (Blueprint $table) {
            $table->string('first_name',100)->default('');
            $table->string('last_name',100)->default('');
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
            $table->dropColumn(['first_name','last_name']);
        });
    }
}
