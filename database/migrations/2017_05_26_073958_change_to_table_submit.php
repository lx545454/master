<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeToTableSubmit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submit', function (Blueprint $table) {
            $table->dateTime('processing_time')->nullable()->comment('处理时间')->after('is_agree');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submit', function (Blueprint $table) {
            $table->dropColumn('processing_time');
        });
    }
}
