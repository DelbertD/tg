<?php
/**
 * Created by PhpStorm.
 * User: lovey
 * Date: 2018/4/11
 * Time: 17:36
 */

namespace app\index\model;


use think\Model;

class Patient extends Model
{

    protected $pk = 'id';

    protected $table = 'tg_patient';

    public function getSexAttr($value){
        $sex = [1 => '男',2 => '女'];
        return $sex[$value];
    }

    public function getFlagAttr($value){
        $flag = [1 => '是',2 => '否'];
        return $flag[$value];
    }
}