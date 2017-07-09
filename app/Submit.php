<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Submit extends Model
{
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'submit';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'f_id', 'u_id', 'form_content', 'is_agree', 'processing_time'
    ];

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;
}
