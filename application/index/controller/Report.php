<?php
/**
 * Created by PhpStorm.
 * User: lovey
 * Date: 2018/4/12
 * Time: 12:03
 */

namespace app\index\controller;

use think\Db;
class Report extends Common
{
    public function dels($id = ''){
        $ids = explode(',', $id);
        $res = Db::name('h_report')
            ->where('id', 'in', $ids)
            ->update(['is_del' => 2]);
        if ($res){
            $this->success('报告成功删除', 'index/hand/index/type/3/t/' . mt_rand(1, 999));
        }else{
            $this->error('报告删除失败', 'index/hand/index/type/3');
        }
    }
}