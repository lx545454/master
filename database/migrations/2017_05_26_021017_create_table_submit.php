<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableSubmit extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submit', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('f_id')->comment('表单ID');
            $table->integer('u_id')->comment('用户ID');
            $table->timestamp('sub_time')->comment('提交时间');
            $table->tinyInteger('is_agree')->default(0)->comment('1 通过; 2 拒绝');
            $table->timestamp('processing_time')->comment('处理时间');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('submit');
    }
}
