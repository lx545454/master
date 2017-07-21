<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Advice extends Model
{
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'advice';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'content', 'userid', 'created_at', 'is_read'
    ];

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;
}
