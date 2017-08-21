<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dicofnum extends Model
{
    /**
     * 与模型关联的数据表
     *
     * @var string
     */
    protected $table = 'dicodnum';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wan','qian', 'bai', 'shi','ge','num','prize','big','zong'
    ];

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;
}
