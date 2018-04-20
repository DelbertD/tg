<?php
/**
 * Created by PhpStorm.
 * User: lovey
 * Date: 2018/4/3
 * Time: 9:46
 */

namespace app\index\controller;


use think\Controller;
use think\Db;
use think\Cache;
class Common extends Controller
{

    protected function _list($dbQuery = null, $row_page = 10, $isPage = true, $isDisplay = true, $total = false)
    {
        $db = is_null($dbQuery) ? Db::name($this->table) : (is_string($dbQuery) ? Db::name($dbQuery) : $dbQuery);
        $row_page = $this->request->get('limit', $row_page, 'intval');
        // 列表数据查询与显示
        $result = [];
        if ($isPage) {
            $page = $db->paginate($row_page, $total, ['query' => $this->request->get()]);
            $result['list'] = $page->all();
            $result['page'] = preg_replace(['|href="(.*?)"|'], ['data-open="$1" href="javascript:void(0);"'], $page->render());
        } else {
            $result['list'] = $db->select();
        }
        if (false === $this->_callback('_data_filter', $result['list']) || !$isDisplay) {
            return $result;
        }
        return $result;

    }

    /**
     * 当前对象回调成员方法
     * @param string $method
     * @param array|bool $data
     * @return bool
     */
    protected function _callback($method, &$data)
    {
        foreach ([$method, "_" . $this->request->action() . "{$method}"] as $_method) {
            if (method_exists($this, $_method) && false === $this->$_method($data)) {
                return false;
            }
        }
        return true;
    }
}