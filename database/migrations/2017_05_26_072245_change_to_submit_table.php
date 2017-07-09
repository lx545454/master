<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeToSubmitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submit', function (Blueprint $table) {
            $table->dropColumn('sub_time');
            $table->dropColumn('processing_time');
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
            $table->timestamp('sub_time')->comment('提交时间');
            $table->timestamp('processing_time')->comment('处理时间');
        });
    }
}
