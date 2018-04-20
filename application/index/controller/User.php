<?php
/**
 * Created by PhpStorm.
 * User: lovey
 * Date: 2018/4/12
 * Time: 17:57
 */

namespace app\index\controller;


use think\Db;

class User extends Common
{
    public function chPwd(){
        if (request()->isGet()){
            $url = request()->url();
            $this->assign('url', $url);
        }
    }


    public function lists(){
        if (request()->isGet()){
            $url = request()->url();
            $keyword =request()->get('keyword', '');
            if ($keyword){
                if (checkPhone($keyword)){
                    $field = 'tel';
                }elseif (checkID($keyword)){
                    $field = 'id_card';
                }elseif (checkName($keyword)){
                    $field = 'name';
                }else{
                    $field = '';
                }
            }
            $db = Db::name('admin')
                ->where('level', 2)
                ->where('is_del', 1)
                ->field("*,from_unixtime(addtime, '%Y-%m-%d') as at,from_unixtime(lastlogtime, '%Y-%m-%d') as lt,inet_ntoa(lastlogip) as ip")
                ->order('id desc');
            if ($keyword){
                $db = $field ? $db->where($field, $keyword) : $db->where('1!=1');
            }
            $result =  parent::_list($db);
            $this->assign('url', $url);
            $this->assign('keyword', $keyword);
            return $this->fetch('', $result);
        }
    }

    protected function _lists_data_filter(&$data){
        foreach ($data as &$v){
            if (isset($v['pass'])){
                unset($v['pass']);
            }
        }
    }

    public function add(){
        if (request()->isGet()){
            $xueli = [
                '其他',
                '初中',
                '高中',
                '本科',
                '硕士',
                '博士'
            ];
            $this->assign('xueli', $xueli);
            $url = request()->url();
            $this->assign('url', $url);
            return $this->fetch();
        }else{
            $post = request()->post();
            $id_card = $post['id_card'];
            $user = Db::name('admin')
                ->where('id_card', $id_card)
                ->find();
            if ($user){
                $this->error('用户已经存在', '');
            }
            $insert = [];
            $insert['name'] = $post['name'];
            $insert['pass'] = md5($post['pass']);
            $insert['tel']  = $post['tel'];
            $insert['sex']  = $post['sex'];
            $insert['age']  = $post['age'];
            $insert['desc'] = $post['desc'];
            $insert['xueli']   = $post['xueli'];
            $insert['addtime'] = time();
            $insert['id_card'] = $id_card;
            $insert['depa_id'] = $post['depa_id'];
            $insert['is_use']  = isset($post['is_use']) ? $post['is_use'][0] : 2;
            $ok = Db::name('admin')->insert($insert);
            if ($ok){
                $this->success('用户添加成功','');
            }else{
                $this->error('用户添加失败', '');
            }
        }
    }


    public function edit($id = 0){
        if (request()->isGet()){
            $url = request()->url();
            $xueli = [
                '其他',
                '初中',
                '高中',
                '本科',
                '硕士',
                '博士'
            ];
            $user = Db::name('admin')->find($id);
            $this->assign('xueli', $xueli);
            $this->assign('url', $url);
            $this->assign('user', $user);
            return $this->fetch('user/add');
        }else{
            $post = request()->post();
            $id_card = $post['id_card'];
            $id = $post['id'];
            $user = Db::name('admin')
                ->where('id_card', $id_card)
                ->find();
            if ($user && $user['id'] != $id){
                $this->error('身份证已经被使用', '');
            }
            $save = [];
            $save['id']   = $id;
            $save['name'] = $post['name'];
            $save['tel']  = $post['tel'];
            $save['sex']  = $post['sex'];
            $save['age']  = $post['age'];
            $save['desc'] = $post['desc'];
            $save['xueli']   = $post['xueli'];
            $save['id_card'] = $id_card;
            $save['depa_id'] = $post['depa_id'];
            $save['is_use']  = isset($post['is_use']) ? $post['is_use'][0] : 2;
            $save['up_time'] = time();
            $ok = Db::name('admin')->update($save);
            if ($ok){
                $this->success('用户修改成功','');
            }else{
                $this->error('用户修改失败', '');
            }
        }
    }

    public function dels($id = ''){
        $ids = explode(',', $id);
        $res = Db::name('admin')
            ->where('id', 'in', $ids)
            ->update(['is_del' => 2]);
        if ($res){
            $this->success('用户成功删除', 'index/user/lists/t/' . mt_rand(1, 999));
        }else{
            $this->error('用户删除失败');
        }
    }

    public function qyUser($id = 0, $val = 2){
        $ret = [
            'msg' => '操作失败',
        ];
        $ok = Db::name('admin')
            ->where('id', $id)
            ->update(['is_use' => $val]);
        if ($ok && $val){
            $ret['msg'] = '操作成功';
            return $ret;
        }
        return $ret;
    }

}