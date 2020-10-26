<?php
/**
 * 继承系统默认的grid类
 */
namespace webadmin\widgets;

use Yii;

class GridView extends \yii\grid\GridView
{
    public $layout = "{items}\n<div class='row margin-top-10 margin-bottom-10'><div class='col-xs-12 text-center'><div class='pull-left'>{summary}</div>{pager}</div></div>";
    
    /**
     * 重置分页标签项
     */
    public function renderPager()
    {
        $pagination = $this->dataProvider->getPagination();
        if ($pagination === false || $this->dataProvider->getCount() <= 0) {
            return '';
        }
        /* @var $class LinkPager */
        $pager = $this->pager;
        $class = \yii\helpers\ArrayHelper::remove($pager, 'class', \webadmin\widgets\LinkPager::className());
        $pager['pagination'] = $pagination;
        $pager['view'] = $this->getView();
        
        return $class::widget($pager);
    }
}