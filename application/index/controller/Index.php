<?php
/**
 * Created by PhpStorm.
 * User: wyg
 * Date: 2018/3/6 0006
 * Time: 下午 5:20
 */

namespace app\index\controller;

use think\Db;


class index extends Common
{
    public function index(){
        $nav = Db::name('nav')
            ->where('is_show', 1)
            ->select();
        $this->assign('nav', $nav);
        return $this->fetch();
    }

    public function main(){
        return view();
    }

}