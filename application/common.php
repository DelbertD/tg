<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

use think\Cache;
use think\Db;

if (!function_exists('checkPhone')){
    function checkPhone($val){
        $partten = '/^1[35789]\d{9}$/';
        $rs  = preg_match($partten, $val);
        return $rs ? true : false;
    }
}

if (!function_exists('checkID')){
    function checkID($sfz)
    {
        $sfz = strtoupper(trim($sfz));
        $len = strlen($sfz);
        if ($len != 18) {
            return false;
        }
        $a = str_split($sfz, 1);
        $w = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];
        $c = [1, 0, 'X', 9, 8, 7, 6, 5, 4, 3, 2];
        $sum = 0;
        for ($i = 0; $i < 17; $i++) {
            $sum = $sum + $a[$i] * $w[$i];
        }
        $r = $sum % 11;
        $res = $c[$r];
        if ($res == $a[17]) {
            return true;
        } else {
            return false;
        }
    }
}

if (!function_exists('checkName')){
    function checkName($name){
        $rs = preg_match('/^[\x{4e00}-\x{9fa5}]+$/u', trim($name));
        return $rs ? true : false;
    }

}

if (!function_exists('getCatchVar')){
    function getCatchVar($var)
    {
        if (strpos($var, '.')) {
            $list = explode('.', $var, 2);
            $key = $list[0];
            $name = $list[1];
            if (Cache::has($key)) {
                $data = Cache::get($key);
                return isset($data[$name]) ? $data[$name] : null;
            }
            else{
                return null;
            }
        } else {
            $key = $var;
            if (Cache::has($key)){
                $data = Cache::get($key);
                return $data ? $data : null;
            }
            else{
                return null;
            }
        }
    }
}

if (!function_exists('getVal')){
    function getVal($str){
        if (!empty($str)){
            list($dbName, $field, $id) = explode('.',  $str);
            return Db::name($dbName)->where('id', $id)->value($field);
        }else{
            return '';
        }
    }
}


if(!function_exists('checkCom'))
{
    function checkCom()
    {
        $i=1;
        $comArr = [];
        while ($i<10)
        {
            exec("mode " . 'COM'.$i . " BAUD=115200" . " PARITY=n DATA=8 STOP=1 odsr=off",$out, $ok);
            if($ok==0)
            {
                $res = @fopen('COM'.$i,'w+');
                if($res)
                {
                    $comArr[]='COM'.$i;
                    fclose($res);
                }
            }
           $i++;
        }
        return $comArr;
    }
}


