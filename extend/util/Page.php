<?php
namespace util;

// +----------------------------------------------------------------------
// | ThinkPHP 5 分页扩展
// +---------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +---------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://itzxf.com All rights reserved.
// +---------------------------------------------------------------------
// | Author:ZhaoXianFang <1748331509@qq.com>
// +---------------------------------------------------------------------

use think\Paginator;

/**
 * 使用
 * $pageData = Page::make($items, $length, $page, $total, $simple = false, $options = []);
 * $page     = $pageData->render();
 * $this->view->assign('page', $page);
 */
class Page extends Paginator
{
    //首页
    protected function home()
    {
        if (request()->isMobile()) {
            return '';
        }
        if ($this->currentPage() > 1) {
            return "<a href='" . $this->url(1) . "' title='首页'>首页</a>";
        } else {
            return "<p class='disabled'><span >首页</span></p>";
        }
    }
    //上一页
    protected function prev()
    {
        if ($this->currentPage() > 1) {
            return "<a href='" . $this->url($this->currentPage - 1) . "' title='上一页'>上一页</a>";
        } else {
            return "<p class='disabled'><span >上一页</span></p>";
        }
    }
    //下一页
    protected function next()
    {
        if ($this->hasMore) {
            return "<a href='" . $this->url($this->currentPage + 1) . "' title='下一页'>下一页</a>";
        } else {
            return "<p class='disabled'><span >下一页</span></p>";
            // return"<a href='javascript:;' class='disabled'>下一页</a>";
        }
    }
    //尾页
    protected function last()
    {
        if (request()->isMobile()) {
            return '';
        }
        if ($this->hasMore) {
            return "<a href='" . $this->url($this->lastPage) . "' title='尾页'>尾页</a>";
        } else {
            // return "<p>尾页</p>";
            return "<p class='disabled'><span >尾页</span></p>";
        }
    }
    //统计信息
    protected function info()
    {
        return "<p class='pageRemark'>共<b>" . $this->lastPage .
        "</b>页<b>" . $this->total . "</b>条数据</p>";
    }
    /**
     * 页码按钮
     * @return string
     */
    protected function getLinks()
    {
        $isMobile = request()->isMobile();

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
            $block['first'] = $this->getUrlRange(1, $window + ($isMobile ? 0 : 2));
            $block['last']  = $this->getUrlRange($this->lastPage - ($isMobile ? 0 : 1), $this->lastPage);
        } elseif ($this->currentPage > ($this->lastPage - $window)) {
            $block['first'] = $this->getUrlRange(1, ($isMobile ? 1 : 2));
            $block['last']  = $this->getUrlRange($this->lastPage - ($window + ($isMobile ? -1 : 2)), $this->lastPage);
        } else {
            $block['first']  = $this->getUrlRange(1, ($isMobile ? 1 : 2));
            $block['slider'] = $this->getUrlRange($this->currentPage - $side + ($isMobile ? 1 : 0), $this->currentPage + $side - ($isMobile ? 1 : 0));
            $block['last']   = $this->getUrlRange($this->lastPage - ($isMobile ? 0 : 1), $this->lastPage);
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
                    '%s<div class="pagination">%s %s %s</div>',
                    $this->css(),
                    $this->prev(),
                    $this->getLinks(),
                    $this->next()
                );
            } else {
                return sprintf(
                    '%s<div class="pagination">%s %s %s %s %s %s</div>',
                    $this->css(),
                    $this->home(),
                    $this->prev(),
                    $this->getLinks(),
                    $this->next(),
                    $this->last(),
                    $this->info()
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
        return '<a href="' . htmlentities($url) . '" title="第"' . $page . '"页" >' . $page . '</a>';
    }
    /**
     * 生成一个禁用的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getDisabledTextWrapper($text)
    {
        return '<p class="pageEllipsis">' . $text . '</p>';
    }
    /**
     * 生成一个激活的按钮
     *
     * @param  string $text
     * @return string
     */
    protected function getActivePageWrapper($text)
    {
        return '<a href="" class="cur">' . $text . '</a>';
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
    protected function getPageLinkWrapper($url, $page)
    {
        if ($page == $this->currentPage()) {
            return $this->getActivePageWrapper($page);
        }
        return $this->getAvailablePageWrapper($url, $page);
    }
    /**
     * 分页样式
     */
    protected function css()
    {
        $pcCss = '  <style type="text/css">
            .pagination p{
                margin:0;
                cursor:pointer
            }
            .pagination{
                height:40px;
                padding:20px 0px;
            }
            .pagination a:hover{
                color:#077ee3;
                background: white;
                border:1px #077ee3 solid;
            }
            .pagination a.cur{
                border:none;
                background:#077ee3;
                color:#fff;
            }
            .pagination p{
                position: relative;
                float: left;
                padding: 6px 12px;
                margin-left: 2px;
                line-height: 1.42857143;
                color: #337ab7;
                text-decoration: none;
                background-color: #fff;
                border: 1px solid #ddd;
            }
            .pagination p.pageRemark{
                border-style:none;
                background:none;
                margin-right:0px;
                padding:4px 0px;
                color:#666;
            }
            .pagination p.pageRemark b{
                color:red;
            }
            .pagination p.pageEllipsis{
                border-style:none;
                background:none;
                padding:4px 0px;
                color:#808080;
            }
            .dates li {font-size: 14px;margin:20px 0}
            .dates li span{float:right}
            .pagination>a{
                position: relative;
                float: left;
                padding: 6px 12px;
                margin-left: 2px;
                line-height: 1.42857143;
                color: #337ab7;
                text-decoration: none;
                background-color: #fff;
                border: 1px solid #ddd;
            }
        </style>';
        $mobileCss = '';
        if (request()->isMobile()) {
            $mobileCss = $this->cssEnhance();
        }
        return $pcCss . $mobileCss;
    }

    //手机端增强
    public function cssEnhance()
    {
        return '  <style type="text/css">
            .pagination>a{
                padding: 4px 9px;
            }
            .pagination p{
                padding: 4px 9px;
                margin-left: 2px;
            }
        </style>';
    }
}
