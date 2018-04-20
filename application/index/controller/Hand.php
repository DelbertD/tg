<?php
/**
 * Created by PhpStorm.
 * User: lovey
 * Date: 2018/4/3
 * Time: 15:00
 */

namespace app\index\controller;


use think\Db;
use think\Cache;
class Hand extends Common
{

    public function index(){
        $request = $this->request;
        $type = input('param.type',1);
        $pid = getCatchVar('curPat.id');
        $url = $request->url(true);
        //患者列表
        if (Cache::has('list')){
            $patients = Cache::get('patients');
        }else{
            $patients = Db::name('patient')
                ->where('is_del',1)
                ->where('flag', 1)
                ->order('id desc')
                ->select();
            Cache::set('patients', $patients, 24 * 3600);
        }
        //评估数据
        if ($type == 1){
            $data = Db::name('h_exe')
                ->alias('he')
                ->join('imgs m','m.id = he.icon_id')
                ->field('he.id,he.name as en,he.path as hpath,m.path as mp')
                ->where('type', $type)
                ->select();
            //评估序列
            $pg = Cache::get('list-'. $pid);
            $this->assign([
                'data' => $data,
                'pg'   => $pg
            ]);
        }
        //游戏数据获取
        if ($type == 2){
            $data = Db::name('h_exe')
                ->alias('he')
                ->join('imgs m','m.id = he.icon_id')
                ->field('he.id,he.name as en,he.path as hpath,m.path as mp')
                ->where('type', $type)
                ->select();

            //游戏处方获取
            $yxcf = Db::name('h_cf')
                ->alias('c')
                ->join('h_exe e', 'e.id = c.exe_id')
                ->where('pid', $pid)
                ->field('c.*,e.name as en')
                ->order('sort asc')
                ->select();
            //获取历史处方
            if($pid)
            {
                $cfList = db('h_cflist')->where('pid',$pid)->select();

            }else{
                $cfList = NULL;
            }
            $this->assign([
                'data'  => $data,
                'yxcf'  => $yxcf,
                'cfList'=> $cfList
            ]);
        }
        //报告数据获取
        if ($type == 3){
            $uid = request()->param('uid');
            $pid = request()->param('pid');
            $e_t = request()->param('e_t');
            $db = Db::name('h_report')
                ->where('is_del', 1)->whereNotIn('type',3);
            $db = $uid ? $db->where('uid', $uid) : $db;
            $db = $pid ? $db->where('pid', $pid) : $db;
            $db = $e_t ? $db->where('type', $e_t) : $db;
            //下拉列表数据获取
            $user = Db::name('admin')
                ->where('level', 2)
                ->where('is_use', 1)
                ->select();
            $result = parent::_list($db);
            $this->assign([
                'page' => $result['page'],
                'list' => $result['list'],
                'user' => $user,
                'uid'  => $uid,
                'pid'  => $pid,
                'e_t'  => $e_t
            ]);

        }
        //公共数据获取
        $this->assign([
            'patients' => $patients,
            'type'     => $type,
            'url'      => $url
        ]);
        return $this->fetch();
    }

    /**
     * @return mixed
     */
    public function setConf(){
        $request = $this->request;
        $id = input('param.id');
        if ($request->isGet()){
            $url = $request->url(true);
            $this->assign('url', $url);
            return $this->fetch();
        }
        if ($request->isPost()){
            $post = $request->post();
            $lor = $post['lor'];
            //发送必要配置文件
            $data = [
                'lor'  => $lor
            ];
            $this->creatJsonForExe($id, $data, 'h_exe');
            //正式环境$uid无需传入
            $extra['lor'] = $lor;
            //执行评估
            $ret = $this->doExe($id, $extra, 'h_exe');
            if ($ret['suc'] == 1){
                $this->success($ret['msg'], 'index/hand/index/id=' . $id);
            }else{
                $this->error($ret['msg'], 'index/hand/index/id=' . $id);
            }
        }
    }

    //生成set.j文件
    private function creatJsonForExe($id, $data, $table){
        $path = config('exe.path');
        $eName = getVal($table . '.path.' . $id);
        $dir = explode('/',$eName);
        $json = json_encode($data);
        $file1 = $path . 'set.j';
        $file2 = $path . $dir[0] . '/' . $dir[1] . '/set.j';
        $res1 = file_put_contents($file1, $json);
        $res2 = file_put_contents($file2, $json);
        if ($res1 && $res2){
            return true;
        }
        return false;
    }

    //解析exe生成的字符串，返回字段组成的数组
    private function getReport($content){
        $data = json_decode($content, true);
        $con = isset($data['con']) ? $data['con'] : '';
        $retArr = [
            'pz'      => isset($data['pz']) ? $data['pz'] : '',
            'max'     => isset($data['max']) ? $data['max'] : '',
            'min'     => isset($data['min']) ? $data['min'] : '',
            'unit'    => isset($data['unit']) ? $data['unit'] : '',
            'content' => json_encode($con)
        ];
        return $retArr;
    }

    //评估加入序列
    private function catchList($pid, $id){
        $key = 'list-'. $pid;
        if (Cache::has($key)){
            $catchData = Cache::get($key);
        }else{
            $catchData = [];
        }
        $data = [
            'id'   => $id
        ];
        $catchData[] = $data;
        Cache::set('list-'. $pid, $catchData, 2 * 60 * 60);
    }


    //游戏设置以及运行
    public function gameSet(){
        $request = $this->request;
        $id = input('param.id');
        if ($request->isGet()){
            $pz = [
                250  => '250克',
                500 => '500克',
                750 => '750克',
                1000 => '1000克',
                1250 => '1250克',
                1500 => '1500克',
                1750 => '1750克',
                2000 => '2000克',
                2250 => '2250克',
                2500 => '2500克',
            ];

            $sp = [
                '0.5'  => '0.5分钟',
                '1.0'  => '1分钟',
                '1.5'  => '1.5分钟',
                '2.0'  => '2分钟',
                '2.5'  => '2.5分钟',
                '3.0'  => '3分钟',
                '3.5'  => '3.5分钟',
                '4.0'  => '4分钟',
                '4.5'  => '4.5分钟',
                '5.0'  => '5分钟',
            ];
            $url = $request->url(true);
            $xlbw = Db::name('h_exe')
                ->where('bid', '>', 0)
                ->field('id,name,bid')
                ->select();
            $this->assign([
                'url'  => $url,
                'xlbw' => $xlbw,
                'pz'   => $pz,
                'sp'   => $sp
            ]);
            return $this->fetch();
        }
        if ($request->isPost()){
            $post = input('post.');
            $action = $post['action'];
            $uid = session(config('user.user_session_key') . '.id');
            $uid = 1;
            $pid = getCatchVar('curPat.id');
            //游戏加入处方
            if ($action == 'add'){
                $insert['pid'] = $pid;
                $insert['uid'] = $uid;
                $insert['max'] = $post['max'];
                $insert['min'] = $post['min'];
                $insert['lor'] = $post['lor'];
                $insert['bid'] = $post['bid'];
                $insert['lev'] = $post['lev'];
                $insert['pz']  = $post['pz'];
                $insert['unit'] = $post['unit'];
                $insert['msic'] = $post['msic'];
                $insert['exe_id'] = $id;
                $insert['addtime'] = time();
                $insert['sp'] = ceil( $post['sp'] * 60);
                $insert['tm'] = ceil( $post['tm'] * 60);
                $res = Db::name('h_cf')->insert($insert);
                if ($res){
                    $this->success('成功加入处方','index/hand/index/type/2/t/' . mt_rand(1,999));
                }else{
                    $this->error('加入处方失败','index/hand/index/type/2');
                }
            }
            //单独执行游戏
            $pz  = $post['pz'];
            $tm  = $post['tm'];
            $sp  = $post['sp'];
            $lev  = $post['lev'];
            $lor = $post['lor'];
            $max = $post['max'];
            $min = $post['min'];
            $unit = $post['unit'];
            $msic  = $post['msic'];
            //发送必要配置文件
            $data = [
                'pz'   => $pz,
                'tm'   => $tm,
                'sp'   => $sp,
                'lev'  => $lev,
                'max'  => $max,
                'min'  => $min,
                'lor'  => $lor,
                'unit' => $unit,
                'msic' => $msic
            ];
            $this->creatJsonForExe($id, $data, 'h_exe');
            $extra['lor'] = $post['lor'];
            $extra['type'] = 2;
            $res = $this->doExe($id, $extra, 'h_exe');
            if ($res['suc']){
                $this->success($res['msg'], 'index/hand/index/type/2');
            }else{
                $this->error($res['msg'], 'index/hand/index/type/2');
            }
        }
    }


    //从数据报告中获取范围
    public function getRange($bid = 0, $lor = 0){
        $ret = [
            'suc' => 0,
            'msg' => '范围获取失败'
        ];
        $pid = getCatchVar('curPat.id');
        $data = Db::name('h_report')
            ->where('pid', $pid)
            ->where('bid', $bid)
            ->where('lor', $lor)
            ->order('id desc')
            ->field('max,min,unit,pz')
            ->find();
        if (!empty($data)){
            $ret['suc'] = 1;
            $ret['pz']  = $data['pz'];
            $ret['msg'] = '成功获取到范围';
            $ret['max'] = $data['max'];
            $ret['min'] = $data['min'];
            $ret['unit'] = $data['unit'];
            return $ret;
        }else{
            return $ret;
        }
    }

    /**
     * 修改排序
     * @param int $id
     * @param int $val
     * @return array
     */
    public function changVal($id = 0,$val = 0){
        $ret = ['suc' => 0];
        if (!empty($val)){
            $res = Db::name('h_cf')
                ->where('id', $id)
                ->update([
                    'sort' => $val
                ]);
            if ($res){
                $ret['suc'] = 1;
                return $ret;
            }
        }
        return $ret;
    }

    //游戏处方开始
    public function start($yxcf = 0, $id = ''){
        $ret = [
            'suc' => 1,
            'msg' => '处方已全部运行完毕'
        ];
        $pid = getCatchVar('curPat.id');
        //清除列表
        if ($yxcf == 1){
            $ids = explode(',', $id);
            $ok = Db::name('h_cf')
                ->where('id', 'in', $ids)
                ->delete();
            if ($ok){
                $this->success('删除成功', 'index/hand/index/type/2/t/' . mt_rand(1,999));
            }else{
                $this->error('删除失败', 'index/hand/index/type/2');
            }
        }
        $yxcf = Db::name('h_cf')
            ->alias('c')
            ->join('h_exe e', 'e.id = c.exe_id')
            ->where('pid', $pid)
            ->where('status', 1)
            ->field('c.*,e.name as en,e.path as dir')
            ->order('sort asc')
            ->find();
        $count = Db::name('h_cf')
            ->where('pid', $pid)
            ->where('status', 1)
            ->count();
        if ($yxcf){
            //此处需要约束范围，因此max，min是必要的
            $data = [
                'pz'   => $yxcf['pz'],
                'tm'   => $yxcf['tm'],
                'sp'   => $yxcf['sp'],
                'lev'  => $yxcf['lev'],
                'max'  => $yxcf['max'],
                'min'  => $yxcf['min'],
                'lor'  => $yxcf['lor'],
                'unit' => $yxcf['unit'],
                'msic' => $yxcf['msic']
            ];

            $exe_id = $yxcf['exe_id'];
            $this->creatJsonForExe($exe_id, $data, 'h_exe');
            $extra['bid'] = $yxcf['bid'];
            $extra['lor'] = $yxcf['lor'];
            $extra['type'] = 2;
            $res = $this->doExe($exe_id, $extra,'h_exe');
            if ($res['suc']){
                $this->chStatus($yxcf['id'], 2);
                if ($count == 1){
                    $ret['suc'] = 0;
                    return $ret;
                }
                $ret['msg'] = $res['msg'];
                $ret['sp'] = $yxcf['sp'];
                return $ret;
            }else{
                $this->chStatus($yxcf['id'], 3);
                $ret['suc'] = 0;
                $ret['msg'] = $res['msg'];
                return $ret;
            }
        }else{
            $ret['suc'] = 0;
            return $ret;
        }

    }

    private function chStatus($id, $status){
        $res = Db::name('h_cf')->update([
            'id'     => $id,
            'status' => $status
        ]);
        return $res;
    }

    //执行exe并生成报告
    private function doExe($exe_id, $extra = [], $table){
        $uid = session(config('user.user_session_key') . '.id');
        //正式环境注释$uid=1
        $uid = 1;
        $pid = getCatchVar('curPat.id');
        $bid = getVal('h_exe.bid.' . $exe_id);
        $ret = [
            'suc'  => 1,
            'msg'  => '应用无法运行',
            'code' => 99
        ];
        $path = config('exe.path');
        $dir  = getVal($table . '.path.' . $exe_id);
        $exe = $path . $dir;
        if (!file_exists($exe)){
            $ret['suc'] = 0;
            $ret['msg'] = '应用正在研发中,请等待';
            $ret['code'] = -99;
        }
        $reportPath = $path . 'result.j';
        if (file_exists($reportPath)){
            unlink($reportPath);
        }
        $btime = time();
        exec($exe, $out, $ok);
        $etime = time();
        if ($ok == 0){
            //检查报告文件生成情况
            if (!file_exists($reportPath)){
                $ret['msg']  = '应用结束，报告未生成';
                $ret['code'] = 1;
                return $ret;
            }
            $str = file_get_contents($reportPath);
            if (!$str){
                $ret['msg'] = '应用结束，报告为空';
                $ret['code'] = 2;
                return $ret;
            }
            //报告文件转化为入库数据
            $content = json_decode($str, true);
            $con = isset($content['con']) ? json_encode($content['con']) : '';
            $data['pid']     = $pid;
            $data['uid']     = $uid;
            $data['bid']     = $bid;
            $data['exe_id']  = $exe_id;
            $data['btime']   = $btime;
            $data['etime']   = $etime;
            $data['addtime'] = time();
            $data['type']    = 1;
            $data['pz']      = isset($content['pz'])   ? $content['pz'] : 0;
            $data['max']     = isset($content['max'])  ? $content['max'] : 0;
            $data['min']     = isset($content['min'])  ? $content['min'] : 0;
            $data['unit']    = isset($content['unit']) ? $content['unit'] : 0;
            $data['content'] = $con;
            $data = array_merge($data, $extra);
            $res = Db::name('h_report')->insert($data);
            if ($res){
                $ret['msg'] = '应用结束，报告已保存';
                $ret['code'] = 3;
                return $ret;
            }else{
                $ret['msg'] = '应用结束，报告未保存';
                $ret['code'] = 4;
                return $ret;
            }
        }else{
            $ret['suc'] = 0;
            return $ret;
        }
    }

    public function saveCf($id = '',$name = '')
    {
        if (request()->isGet()){
            $this->assign('url',request()->url());
            return $this->fetch();
        }else{
            //处理处方表数据
            $res = db('h_cf')->whereIn('id',$id)->select();
            if(!$res)
            {
                $this->error('未能成功提取处方列表!','index/hand/index/type/2/t/'.mt_rand(0,9999));
            }
            foreach ($res as $k=>$v)
            {
                unset($res[$k]['id']);
            }
            //保存的处方加入处方库
            $result = db('h_copycf')->insertAll($res);
            if(!$result)
            {
                $this->error('没有记录被保存！','index/hand/index/type/2/t/'.mt_rand(0,9999));
            }
            //提取处方列表ids
            $idsArr = db('h_copycf')->order('id desc')->limit($result)->column('id');
            $ids = implode($idsArr,',');
            //更改处方列表状态
            db('h_copycf')->whereIn('id',$ids)->setField('status',1);
            //保存处方
            $data['name']    = $name;
            $data['content'] = $ids;
            $data['pid']     = getCatchVar('curPat.id');
            $ok = db('h_cflist')->insert($data);
            if(!$ok)
            {
                //如果入库失败，删除新库数据
                db('h_copycf')->whereIn('id',$ids)->delete();
                $this->error('未能成功加入处方，请重试！');
            }
            db('h_cf')->whereIn('id',$id)->delete();
            $this->success('保存处方成功！','index/hand/index/type/2/t/'.mt_rand(0,9999));
        }
    }

    /**
     *选择历史处方
     * @param string $id
     */
    public function selectCf($id = '')
    {
        $cfData = db('h_cflist')->where('id',$id)->find();
        if(!$cfData)
        {
            return ['suc'=>0,'msg'=>'该历史处方不存在，请新建处方！'];
        }
        //获取处方设置信息具体内容
        $ids = $cfData['content'];
        $cfList = db('h_copycf')->whereIn('id',$ids)->select();
        if(!$cfList)
        {
            db('h_cflist')->where('id',$id)->delete();
            return ['suc'=>0,'msg'=>'该历史处方设置文件不存在，请新建处方！'];
        }
        foreach ($cfList as $k=>$v)
        {
            unset($cfList[$k]['id']);
        }

        $listRes = db('h_cf')->insertAll($cfList);
        if(!$listRes)
        {
            return ['suc'=>0,'msg'=>'该历史处方文件未成功写入，请重试！'];
        }
        $idsArr = db('h_cf')->order('id desc')->limit($listRes)->column('id');
        $cfIds = implode($idsArr,',');
        db('h_cf')->whereNotIn('id',$cfIds)->delete();
        return ['suc'=>1,'msg'=>'已加载历史处方！'];
    }


    //修改处方中游戏设置参数
    public function editCf($id = '')
    {
        if(request()->isGet())
        {
            $pz = [
                250  => '250克',
                500 => '500克',
                750 => '750克',
                1000 => '1000克',
                1250 => '1250克',
                1500 => '1500克',
                1750 => '1750克',
                2000 => '2000克',
                2250 => '2250克',
                2500 => '2500克',
            ];

            $sp = [
                '0.5'  => '0.5分钟',
                '1.0'  => '1分钟',
                '1.5'  => '1.5分钟',
                '2.0'  => '2分钟',
                '2.5'  => '2.5分钟',
                '3.0'  => '3分钟',
                '3.5'  => '3.5分钟',
                '4.0'  => '4分钟',
                '4.5'  => '4.5分钟',
                '5.0'  => '5分钟',
            ];
            //获取旧数据
            $old = db('h_cf')->where('id',$id)->find();
            //获取所有训练部位
            $bw = db('h_bw')->select();
            $this->assign([
                'url'     => request()->url(),
                'pz'      => $pz,
                'sp'      => $sp,
                'bw'      => $bw,
                'old' => $old
            ]);

            return $this->fetch();
        }else{
            //修改数据
            $data = input('post.');
            $data['tm'] *= 60;
            $data['sp'] *= 60;
            $result = db('h_cf')->where('id',$id)->update($data);
            if(!$result)
            {
                $this->error('没有设置被修改！');
            }
            $this->success('修改设置成功！','index/hand/index/type/2/t/'.mt_rand(0,9999));
        }
    }


    /**
     * 执行exe获取范围
     * @param int $bid
     * @param int $lor
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function getValByExe($bid = 0, $lor = 0){
        $pid = getCatchVar('curPat.id');
        $ret = [
            'suc' => 0,
            'msg' => '获取范围失败'
        ];
        //根据部位bid查找exe的id
        $id = Db::name('h_exe')
            ->where('bid', $bid)
            ->value('id');
        if (!$id){
            return $ret;
        }
        $extra['lor'] = $lor;;
        $extra['type'] = 1;
        $res = $this->doExe($id, $extra, 'h_exe');
        //执行成功，报告已保存
        if ($res['suc'] == 1 && $res['code'] == 3){
            $ret = $this->getRange($bid, $lor);
            //将范围更新到数据库
            if ($ret['suc'] == 1){
                $save = [];
                $save['max']  = $ret['max'];
                $save['min']  = $ret['min'];
                $save['unit'] = $ret['unit'];
                $save['pz']   = $ret['pz'];
                Db::name('h_report')
                    ->where('pid', $pid)
                    ->where('bid', $bid)
                    ->where('lor', $lor)
                    ->order('id desc')
                    ->update($save);
            }
        }
        return $ret;
    }
}