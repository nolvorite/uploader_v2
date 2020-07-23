<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSubfolderColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->string('path',1000)->default('');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->string('relative_path',1000)->default('');
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('path');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('relative_path');
        });

    }
}
