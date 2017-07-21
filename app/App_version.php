<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class App_version extends Model
{
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'app_version';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'm_type','merchant', 'version', 'content'
    ];

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;
}
