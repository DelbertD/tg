<?php
/**
 * Created by PhpStorm.
 * User: lovey
 * Date: 2018/4/8
 * Time: 15:57
 */

namespace app\index\controller;

use think\Db;

class Patient extends Common
{
    public function add(){
        $request = $this->request;
        if ($request->isGet()){
            $url = $request->url();
            $this->assign('url', $url);
            return $this->fetch();
        }else{
            $data = $request->post();
            $data['btime'] = strtotime($data['btime']);
            $ok = Db::name('patient')->insert($data);
            $id = Db::getLastInsID();
            if ($ok){
                $this->success('患者添加成功', 'index/hand/index/id/' . $id);
            }else{
                $this->error('患者添加失败', 'index/hand/index/id/' . $id);
            }
        }
    }
}