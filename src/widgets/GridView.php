<?php
/**
 * 继承系统默认的grid类
 */
namespace webadmin\widgets;

use Yii;
use \yii\helpers\Html;

class GridView extends \yii\grid\GridView
{
    /**
     * 格式化显示
     */
    public $layout = "{items}\n<div class='row margin-top-10 margin-bottom-10'><div class='col-xs-12 text-center'><div class='pull-left'>{summary}</div>{pager}</div></div>";
    
    /**
     * 是否显示当页汇总
     */
    public $showPageSummary = true;
    
    /**
     * 当页汇总数据信息缓存
     */
    protected $totalColumns = [];
    
    /**
     * 所有分页汇总数据
     */
    public $totalRows = false;
    
    /**
     * 跳过不做数据汇总的属性
     */
    public $skip_total = [];
    
    /**
     * 行尾属性
     */
    public $footerRowOptions = ['class'=>'warning'];
    
    /**
     * 初始化
     */
    public function init()
    {
        parent::init();
        
        // 显示所有数据汇总
        $total = $this->totalRows;
        if(!empty($total)){
            foreach($this->columns as $key=>$column){
                $attribute = $column->attribute;
                $attributeValue = (!empty($column->value)&&is_string($column->value) ? $column->value : $column->attribute);
                $attributeValue = $attributeValue ? explode(".", $attributeValue) : [];
                $attributeValue = $attributeValue ? end($attributeValue) : '';
                
                if(!empty($total[$attribute]) || !empty($total[$attributeValue])){
                    $this->showFooter = true;
                    $column->footer = (!empty($total[$attribute]) ? $total[$attribute] : $total[$attributeValue]);
                }
            }
        }
        
        // 显示当页汇总
        if($this->showPageSummary){
            $this->showFooter = true;
        }
    }
    
    /**
     * 输出分页数据
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
    
    /**
     * 输出footer数据
     */
    public function renderTableFooter()
    {
        $content = '';
        if($this->showPageSummary && $this->totalColumns){
            $cells = [];
            foreach ($this->columns as $k=>$column) {
                $cells[] = Html::tag('td', (isset($this->totalColumns[$k]) ? $this->totalColumns[$k] : $this->emptyCell), $column->footerOptions);
            }
            $content .= Html::tag('tr', implode('', $cells), $this->footerRowOptions);
        }
        $cells = [];
        foreach ($this->columns as $column) {
            $cells[] = $column->renderFooterCell();
            if($column->footer){
                $showFooter = true;
            }
        }
        if(!empty($showFooter)) $content .= Html::tag('tr', implode('', $cells), $this->footerRowOptions);
        if ($this->filterPosition === self::FILTER_POS_FOOTER) {
            $content .= $this->renderFilters();
        }
        
        return "<tfoot>\n" . $content . "\n</tfoot>";
    }
    
    /**
     * 输出行数据
     */
    public function renderTableRow($model, $key, $index)
    {
        $cells = [];
        /* @var $column Column */
        foreach ($this->columns as $k=>$column) {
            $cell = $column->renderDataCell($model, $key, $index);
            
            // 记录汇总数据
            if($this->showPageSummary && ($column instanceof \yii\grid\DataColumn)){
                $value = strip_tags($cell);
                $attribute = $column->attribute;
                $attributeValue = (!empty($column->value)&&is_string($column->value) ? $column->value : $column->attribute);
                $attributeValue = $attributeValue ? explode(".", $attributeValue) : [];
                $attributeValue = $attributeValue ? end($attributeValue) : '';
                
                if(strlen($value) && is_numeric($value) && (empty($this->skip_total) 
                    || (is_array($this->skip_total) && !in_array($attribute,$this->skip_total) && !in_array($attributeValue,$this->skip_total)))
                ){ // 汇总
                    if(!isset($this->totalColumns[$k])) $this->totalColumns[$k] = 0;
                    $this->totalColumns[$k] += $value;
                }
            }
            $cells[] = $cell;
        }
        if ($this->rowOptions instanceof Closure) {
            $options = call_user_func($this->rowOptions, $model, $key, $index, $this);
        } else {
            $options = $this->rowOptions;
        }
        $options['data-key'] = is_array($key) ? json_encode($key) : (string) $key;
        
        return Html::tag('tr', implode('', $cells), $options);
    }
}