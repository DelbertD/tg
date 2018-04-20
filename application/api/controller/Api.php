<?php
/**
 * 系统公共接口文件放在这里
 * Created by PhpStorm.
 * User: zhaoby
 * Date: 2018/3/6
 * Time: 14:15
 */

namespace app\api\controller;
use app\index\controller\Common;
use app\index\model\Qc;
use app\index\model\Report;
use think\Cache;
use think\Request;
use think\Db;
class Api extends Common
{
    /**
     * 患者表格数据接口
     */
    public function getpaInfo()
    {

        $count = Db::name('patient')->count();
        $page = request()->param('page',1,'intval');
        $limit = request()->param('limit',10,'intval');
        $keyword = request()->param('keyword');
        if (empty($keyword)){
            $field = '';
        }
        else{
            if (checkPhone($keyword)){
                $field = 'tel';
            }elseif (checkID($keyword)){
                $field = 'card';
            }elseif (checkName($keyword)){
                $field = 'name';
            }else{
                $field = 'num';
            }
        }
        if ($field){
            $paInfo = Db::name('patient')
                ->where($field, $keyword)
                ->order('id desc')
                ->limit(($page-1)*$limit,$limit)
                ->select();
        }
        else{
            $paInfo = Db::name('patient')
                ->order('id desc')
                ->limit(($page-1)*$limit,$limit)
                ->select();
        }
        if ($paInfo){
            foreach ($paInfo as &$val){
                if ($val['sex']==1){
                    $val['sex']="男";
                }
                else{
                    $val['sex']="女";
                }
                if ($val['flag']==1){
                    $val['flag']="是";
                }
                else{
                    $val['flag']="否";
                }
            }
        }
        $data = ['code'=>0,'msg'=>'','count'=>$count,'data'=>$paInfo];
        return json($data);
    }

    /**
     * 医生表格数据接口
     */
    public function getJsonForUsers(Request $request)
    {

        $count = Db::name('admin')->where('level', 2)->count();
        $page = $request->param('page',1,'intval');
        $limit = $request->param('limit',10,'intval');
        $keyword = $request->param('keyword');
        if ($keyword){
            $paInfo = Db::name('admin')
                ->field("*,if(level=2,'评估医生','超级管理员') as le,from_unixtime(ctime, '%Y-%m-%d') as ct,from_unixtime(lastlogtime, '%Y-%m-%d') as lt,inet_ntoa(lastlogip) as ip")
                ->where('name', $keyword)
                ->where('level', 2)
                ->order('id desc')
                ->limit(($page-1)*$limit,$limit)
                ->select();
        }
        else{
            $paInfo = Db::name('admin')
                ->field("*,if(level=2,'评估医生','超级管理员') as le,from_unixtime(ctime, '%Y-%m-%d') as ct,from_unixtime(lastlogtime, '%Y-%m-%d') as lt,inet_ntoa(lastlogip) as ip")
                ->order('id desc')
                ->where('level', 2)
                ->limit(($page-1)*$limit,$limit)
                ->select();
        }
        if ($paInfo){
            foreach ($paInfo as &$val){
                if ($val['sex']==1){
                    $val['sex']="男";
                }
                else{
                    $val['sex']="女";
                }
            }
        }
        $data = ['code'=>0,'msg'=>'','count'=>$count,'data'=>$paInfo];
        return json($data);
    }


    public function setPgr($id = 0){
        $data = ['id' => 0,'name' => ''];
        if ($id == 0){
            $ok = Cache::set('curPat', $data);
        }
        else{
            $name = Db::name('patient')
                ->where('id', $id)
                ->value('name');
            $data['id'] = $id;
            $data['name'] = $name;
            $ok = Cache::set('curPat', $data);
        }
        if ($ok){
            return ['valid' => 1, 'msg' => '缓存成功'];
        }else{
            return ['valid' => 0, 'msg' => '缓存失败'];
        }
    }

    public function getJsonForReports($nid = 0, $sid = 0){
        $uid = request()->param('uid');
        $pid = request()->param('pid');
        $type = request()->param('type');
        $timeRange = request()->param('$timeRane','');
        $page = request()->param('page',1,'intval');
        $limit = request()->param('limit',10,'intval');
        $db = Db::name('report');
        $db = $uid ? $db->where('uid', $uid) : $db;
        $db = $pid ? $db->where('pid', $pid) : $db;
        $db = $type ? $db->where('type', $type) : $db;
        $count = $db->where('nid', $nid)
            ->where('sid', $sid)
            ->count();
        $m =Report::where('nid', $nid)
            ->where('sid', $sid)
            ->field("*,from_unixtime(btime, '%Y-%m-%d %H:%i:%s') as bt,from_unixtime(etime, '%Y-%m-%d %H:%i:%s') as et");
        $m = $uid ? $m->where('uid', $uid) : $m;
        $m = $pid ? $m->where('pid', $pid) : $m;
        $m = $type ? $m->where('type', $type) : $m;
        if ($timeRange != '') {
            list($start, $end) = explode('-', $timeRange);
            $_start = strtotime($start);
            $_end = strtotime($end);
            $m->whereBetween('btime', [$_start, $_end]);
        }
        $reports = $m->limit(($page-1)*$limit,$limit)->order('id desc')->select();
        foreach ($reports as &$v){
            $v['pid'] = isset($v['pid']) ? getVal('patient.name.' . $v['pid']) : $v['pid'];
            $v['uid'] = isset($v['pid']) ? getVal('admin.name.' . $v['uid']) : $v['uid'];
        }
        $data = ['code'=>0,'msg'=>'','count'=>$count,'data'=>$reports];
        return response($data,200, [], 'json');
    }

        public function getSensForSel($id = 0, $flag = 1){
        $sens = Db::name('sen')
            ->where('a_id', $id)
            ->where('flag', $flag)
            ->select();
        $data = [];
        if (!empty($sens)){
            foreach ($sens as $v){
                $items = [];
                $items['name']  = $v['name'];
                $items['value'] = $v['type'];
                $items['unit']  = $v['unit'];
                $data[] = $items;
            }
        }else{
            $items['name']  = '';
            $items['value'] = '';
            $items['unit']  = '';
            $data[] = $items;
        }
        echo json_encode($data);
    }

}