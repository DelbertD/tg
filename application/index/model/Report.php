<?php
/**
 * Created by PhpStorm.
 * User: zhaoby
 * Date: 2018/3/19
 * Time: 9:47
 */

namespace app\index\model;


use think\Model;

class Report extends Model
{
    protected $pk = "id";
    protected $table = "t_h_report";

    public function p(){
        return $this->belongsTo('Patient','pid','id');
    }

    public function u(){
        return $this->belongsTo('User','uid','id');
    }

    public function getTypeAttr($value, $data)
    {
        $type = [1=>'评估',2=>'训练'];
        return $type[$value];
    }

    public function getQcidAttr($value, $data)
    {
        $dbName = '';
        if ($data['type'] == 1){
            $dbName = 'qicai';
        }elseif ($data['type'] == 2){
            $dbName = 'xunlian';
        }elseif ($data['type'] == 3){
            $dbName = 'games';
        }else{

        }
        return getVal($dbName . '.name.' . $value);
    }


   /* public function getContentAttr($value, $data){
        $type = $data['type'];
        $id = $data['id'];
        return formJsonContent($id, $type);
    }*/

    public static function getReportById($id = 0){
        $obj = self::with(['p','u'])->field("*,from_unixtime(btime, '%Y-%m-%d %H:%i:%s') as bt,from_unixtime(etime, '%Y-%m-%d %H:%i:%s') as et")
            ->find($id);
        $obj->append(['content']);
        return $obj;
    }
}