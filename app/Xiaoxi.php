<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Xiaoxi extends Model
{
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'xiaoxi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title','content', 'link', 'created_at', 'type'
    ];

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;
}
