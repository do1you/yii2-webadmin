<?php
/**
 * 继承系统默认的分页内容，增加每页记录数和跳转页码
 */
namespace webadmin\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\data\Pagination;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;


class LinkPager extends \yii\widgets\LinkPager
{
    public $redirectPageLabel = '跳转'; // 页码跳转
    public $pageSizeLabel = '每页显示'; // 页码条数
    public $firstPageLabel = '首页'; // 首页
    public $prevPageLabel = '上一页'; // 上一页
    public $nextPageLabel = '下一页'; // 下一页
    public $lastPageLabel = '尾页'; // 尾页
        
    /**
     * 分页按纽数量
     */
    protected function renderPageButtons()
    {
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }
        
        $buttons = [];
        $currentPage = $this->pagination->getPage();
        
        // first page
        $firstPageLabel = $this->firstPageLabel === true ? '1' : $this->firstPageLabel;
        if ($firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton($firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }
        
        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }
        
        // internal pages
        list($beginPage, $endPage) = $this->getPageRange();
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $buttons[] = $this->renderPageButton($i + 1, $i, null, $this->disableCurrentPageButton && $i == $currentPage, $i == $currentPage);
        }
        
        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }
        
        // last page
        $lastPageLabel = $this->lastPageLabel === true ? $pageCount : $this->lastPageLabel;
        if ($lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton($lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }
        
        // 添加每页显示条数修改
        $linkWrapTag = ArrayHelper::remove($this->linkContainerOptions, 'tag', 'li');
        if($this->pageSizeLabel !== false) {
            $selectPageSizeItem = [
                10 => '10',
                20 => '20',
                30 => '30',
                50 => '50',
                75 => '75',
                100 => '100',
                200 => '200',
            ];
            $url = $this->pagination->createUrl($currentPage);
            $url .= (stripos($url,'?')===false ? '?' : '&').$this->pagination->pageSizeParam.'=';
            $id = md5($url.'_select');
            $selectPageSize = Html::dropDownList('pager', $this->pagination->pageSize, $selectPageSizeItem, ['prompt' => $this->pageSizeLabel, 'id' => $id]);
            $buttons[] = Html::tag($linkWrapTag, $selectPageSize, ['class' => 'select-pagesize']);
            
            // JS
            $script = <<<eot
                $('#{$id}').on('change',function(){
                    var val = $(this).val();
                    val && (location.href = "{$url}" + val);
                });
eot;
            Yii::$app->controller->view && Yii::$app->controller->view->registerJs($script);
        }
        
        // 添加 页码跳转
        if($this->redirectPageLabel !== false) {
            $url = $this->pagination->createUrl(false);
            $url .= (stripos($url,'?')===false ? '?' : '&').$this->pagination->pageParam.'=';
            $id = md5($url.'_text');
            $thePage = intval($currentPage) + 1;
            $number = Html::input('text', 'page', $thePage, ['class' => 'redirect-page form-control', 'style'=>'display:inline-block;width:50px;text-align:center;margin-left:-1px;height: 32px;margin-top:-1px;', 'id' => $id]);
            $buttons[] = Html::tag($linkWrapTag, $number, ['class' => 'redirect-page-num']);
            
            //$redirectButton =  Html::button($this->redirectPageLabel, ['class' => 'btn btn-primary btn-redirect', 'data-count' => $pageCount, 'data-url' => $url]);
            //$buttons[] = Html::tag($linkWrapTag, $redirectButton, ['class' => 'redirect-page-btn']);
            
            // JS
            $script = <<<eot
                $('#{$id}').on('change',function(){
                    var val = $(this).val();
                    val && (location.href = "{$url}" + val);
                });
eot;
            Yii::$app->controller->view && Yii::$app->controller->view->registerJs($script);
        }
        
        $options = $this->options;
        $tag = ArrayHelper::remove($options, 'tag', 'ul');
        return Html::tag($tag, implode("\n", $buttons), $options);
    }
}