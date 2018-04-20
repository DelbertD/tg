<?php
/**
 * Created by PhpStorm.
 * User: lovey
 * Date: 2018/4/11
 * Time: 17:39
 */

namespace app\index\model;


use think\Model;

class User extends Model
{
    protected $pk = "id";
    protected $table="t_admin";

    public function getLevelAttr($value)
    {
        $level = [1=>'超级管理员',2=>'评估医生'];
        return $level[$value];
    }

}