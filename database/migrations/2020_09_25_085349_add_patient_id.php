<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPatientId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
    {
        Schema::create('patient_entries', function (Blueprint $table) {
            $table->increments('patient_id');
            $table->timestamp('report_date');
            $table->integer('pdf_file_id');
            $table->integer('pdf_html_id');
            $table->timestamp('created_at')->nullable();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_entries');

        Schema::table('files', function (Blueprint $table) {

            $table->dropColumn(['patient_id']);

        });
    }
}