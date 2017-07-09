<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeSubmitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submit', function (Blueprint $table) {
            $table->boolean('is_agree')->default(0)->comment('1 通过; 2 拒绝')->change();
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
            $table->tinyInteger('is_agree')->default(0)->comment('1 通过; 2 拒绝')->change();
        });
    }
}
