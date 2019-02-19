<?php

namespace app\cms\model;

use think\Model as ThinkModel;

/**
 * 用户模型
 * @package app\cms\model
 */
class User extends ThinkModel
{
    // 设置当前模型对应的完整数据表名称
    protected $table = '__USER__';

    // 自动写入时间戳
    protected $autoWriteTimestamp = true;
}