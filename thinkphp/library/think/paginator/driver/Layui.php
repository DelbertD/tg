<?php
/**
 * Created by PhpStorm.
 * User: wyg
 * Date: 2018/4/11 0011
 * Time: 下午 10:11
 */

namespace think\paginator\driver;
use think\Paginator;

class Layui extends Paginator
{
    /**
     * 上一页按钮
     * @param string $text
     * @return string
     */
    protected function getPreviousButton($text = "上一页")
    {

        if ($this->currentPage() <= 1) {
            return $this->getDisabledTextWrapper($text);
        }

        $url = $this->url(
            $this->currentPage() - 1
        );

        return $this->getPageLinkWrapper($url, $text);
    }

    /**
     * 下一页按钮
     * @param string $text
     * @return string
     */
    protected function getNextButton($text = '下一页')
    {
        if (!$this->hasMore) {
            return $this->getDisabledTextWrapper($text);
        }

        $url = $this->url($this->currentPage() + 1);

        return $this->getPageLinkWrapper($url, $text);
    }

    /**
     * 页码按钮
     * @return string
     */
    protected function getLinks()
    {
        if ($this->simple) {
            return '';
        }

        $block = [
            'first'  => null,
            'slider' => null,
            'last'   => null,
        ];

        $side   = 3;
        $window = $side * 2;

        if ($this->lastPage < $window + 6) {
            $block['first'] = $this->getUrlRange(1, $this->lastPage);
        } elseif ($this->currentPage <= $window) {
            $block['first'] = $this->getUrlRange(1, $window + 2);
            $block['last']  = $this->getUrlRange($this->lastPage - 1, $this->lastPage);
        } elseif ($this->currentPage > ($this->lastPage - $window)) {
            $block['first'] = $this->getUrlRange(1, 2);
            $block['last']  = $this->getUrlRange($this->lastPage - ($window + 2), $this->lastPage);
        } else {
            $block['first']  = $this->getUrlRange(1, 2);
            $block['slider'] = $this->getUrlRange($this->currentPage - $side, $this->currentPage + $side);
            $block['last']   = $this->getUrlRange($this->lastPage - 1, $this->lastPage);
        }

        $html = '';

        if (is_array($block['first'])) {
            $html .= $this->getUrlLinks($block['first']);
        }

        if (is_array($block['slider'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['slider']);
        }

        if (is_array($block['last'])) {
            $html .= $this->getDots();
            $html .= $this->getUrlLinks($block['last']);
        }

        return $html;
    }

    /**
     * 渲染分页html
     * @return mixed
     */
    public function render()
    {
        if ($this->hasPages()) {
            if ($this->simple) {
                return sprintf(
                    '<ul class="pager">%s %s</ul>',
                    $this->getPreviousButton(),
                    $this->getNextButton()
                );
            } else {
                return sprintf(
                    '<div class="layui-box layui-laypage layui-laypage-defaul">%s %s %s %s </div>',
                    $this->getTotalText(),
                    $this->getPreviousButton(),
                    $this->getLinks(),
                    $this->getNextButton()
                    //$this->getPagesizeSelect(),
                    //$this->goPage()
                );
            }
        }
    }

    /**
     * 生成一个可点击的按钮
     *
     * @param  string $url
     * @param  int    $page
     * @return string
     */
    protected function getAvailablePageWrapper($url, $page)
    {
        return '<a class="normal" href="' . htmlentities($url) . '">' . $page . '</a>';
    }

    /**
     * 生成一个禁用的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<a class="layui-laypage-prev layui-disabled">' . $text . '</a>';
    }

    /**
     * 生成一个激活的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<span style="background-color: #009688;color: #fff"><em>' . $text . '</em></span>';

    }

    /**
     * 生成省略号按钮
     *
     * @return string
     */
    protected function getDots()
    {
        return $this->getDisabledTextWrapper('...');
    }

    /**
     * 批量生成页码按钮.
     *
     * @param  array $urls
     * @return string
     */
    protected function getUrlLinks(array $urls)
    {
        $html = '';

        foreach ($urls as $page => $url) {
            $html .= $this->getPageLinkWrapper($url, $page);
        }

        return $html;
    }

    /**
     * 生成普通页码按钮
     *
     * @param  string $url
     * @param  int    $page
     * @return string
     */
    protected function  getPageLinkWrapper($url, $page)
    {
        if ($this->currentPage() == $page) {
            return $this->getActivePageWrapper($page);
        }

        return $this->getAvailablePageWrapper($url, $page);
    }

    /**
     * 生成总页数提示
     *
     * @param  string $text
     * @return string
     */

    protected function getTotalText($text = '共 %d 条'){
        $text = sprintf($text, $this->total);
        return '<span class="layui-laypage-count">' .$text. '</span>';
    }

    /**
     * 生成分页大小下拉列表
     *
     * @return string
     */

    protected function getPagesizeSelect(){
        $str = '';
        $str .= '<span class="layui-laypage-limits">';
        $str .= '<select id="limit">';
        $str .= '<option value="10">10条/页</option>';
        $str .= '<option value="15">15条/页</option>';
        $str .= '<option value="20">20条/页</option>';
        $str .= '<option value="30">30条/页</option>';
        $str .= '</select>';
        $str .= '</span>';
        return $str;
    }

    /**
     * 跳转到某一页
     *
     * @return string
     */
    protected function goPage(){
        $str = '';
        $str .= '<span class="layui-laypage-skip" style="color: #000;z-index: 999">';
        $str .= '到第';
        $str .= '<input type="text" value="'. $this->currentPage .'" class="layui-input" />';
        $str .= '页';
        $str .= '<button data-open="'.$this->url(1).'" id="go" class="layui-laypage-btn" type="button">确定</button>';
        $str .= '</span>';
        return $str;
    }

}