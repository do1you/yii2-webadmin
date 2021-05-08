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
     * footer放在BODY后面
     */
    public $placeFooterAfterBody = true;
    
    /**
     * 跳过不做数据汇总的属性
     */
    public $skip_total = [];
    
    /**
     * 列合并
     */
    public $colspans = [];
    
    /**
     * 行尾属性
     */
    public $footerRowOptions = ['class'=>'success']; // primary warning info danger success palegreen
    
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
                    $value = (!empty($total[$attribute]) ? $total[$attribute] : $total[$attributeValue]);
                    $column->footer = floatval($value);
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
     * 输出header头
     */
    public function renderTableHeader()
    {
        $content = '';
        if($this->colspans){
            $cells = [];
            foreach ($this->columns as $column) {
                $attribute = $column->attribute;
                $attributeValue = (!empty($column->value)&&is_string($column->value) ? $column->value : $column->attribute);
                $attributeValue = $attributeValue ? explode(".", $attributeValue) : [];
                $attributeValue = $attributeValue ? end($attributeValue) : '';
                
                if(isset($this->colspans[$attribute]) || isset($this->colspans[$attributeValue])){
                    $begin = isset($this->colspans[$attribute]) ? $attribute : $attributeValue;
                    $num = 1;
                }
                
                if(!empty($begin)){
                    if($this->colspans[$begin]['attribute']==$attribute || $this->colspans[$begin]['attribute']==$attributeValue){
                        $cells[] = Html::tag('th', $this->colspans[$begin]['label'], array_merge($column->headerOptions,['colspan'=>$num]));
                        unset($begin);
                    }else{
                        $num++;
                    }                    
                }else{
                    $cells[] = Html::tag('th', $this->emptyCell, $column->headerOptions);
                }
                
            }
            $content .= Html::tag('tr', implode('', $cells), $this->headerRowOptions);
        }
        $cells = [];
        foreach ($this->columns as $column) {
            /* @var $column Column */
            $cells[] = $column->renderHeaderCell();
        }
        $content .= Html::tag('tr', implode('', $cells), $this->headerRowOptions);
        if ($this->filterPosition === self::FILTER_POS_HEADER) {
            $content = $this->renderFilters() . $content;
        } elseif ($this->filterPosition === self::FILTER_POS_BODY) {
            $content .= $this->renderFilters();
        }
        
        return "<thead>\n" . $content . "\n</thead>";
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
                if(count($cells)==0){
                    $cells[] = Html::tag('td', '小计：', array_merge($column->footerOptions,['nowrap'=>'nowrap']));
                }else{
                    $cells[] = Html::tag('td', (isset($this->totalColumns[$k]) ? floatval($this->totalColumns[$k]) : $this->emptyCell), $column->footerOptions);
                }                
            }
            $content .= Html::tag('tr', implode('', $cells), $this->footerRowOptions);
        }
        $cells = [];
        foreach ($this->columns as $column) {
            $cells[] = (count($cells)==0 ? Html::tag('td', '总计：', array_merge($column->footerOptions,['nowrap'=>'nowrap'])) : $column->renderFooterCell());
            if($column->footer){
                $showFooter = true;
            }
        }
        if(!empty($showFooter)){
            $footerRowOptions = $this->footerRowOptions;
            if(!isset($footerRowOptions['class'])) $footerRowOptions['class'] = '';
            $footerRowOptions['class'] .= " warning";
            $content .= Html::tag('tr', implode('', $cells), $footerRowOptions); //  warning info danger success
        }
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
        $num = 0;
        foreach ($this->columns as $k=>$column) {
            $cell = $column->renderDataCell($model, $key, $index);
            
            // 记录汇总数据
            if($this->showPageSummary && ($column instanceof \yii\grid\DataColumn)){
                $value = strip_tags($cell);
                $attribute = $column->attribute;
                $attributeValue = (!empty($column->value)&&is_string($column->value) ? $column->value : $column->attribute);
                $attributeValue = $attributeValue ? explode(".", $attributeValue) : [];
                $attributeValue = $attributeValue ? end($attributeValue) : '';
                
                if($num++ > 1 && strlen($value) && is_numeric($value) && (empty($this->skip_total) 
                    || (is_array($this->skip_total) && !in_array($attribute,$this->skip_total) && !in_array($attributeValue,$this->skip_total)))
                ){ // 汇总
                    if(!isset($this->totalColumns[$k])) $this->totalColumns[$k] = 0;
                    $this->totalColumns[$k] += $value;
                }
            }
            $cells[] = $cell;
        }
        
        // 删除大数
        if($this->totalColumns && is_array($this->totalColumns)){
            foreach ($this->totalColumns as $k=>$value) {
                $value = number_format($value, 3, '.', '');
                if(strlen($value) && (preg_match("/^\d{8,50}/",$value) || (preg_match("/^\d{2,50}$/",$value) && substr($value,0,1)=='0'))){
                    unset($this->totalColumns[$k]);
                }
            }
        }
        
        if ($this->rowOptions instanceof Closure) {
            $options = call_user_func($this->rowOptions, $model, $key, $index, $this);
        } else {
            $options = $this->rowOptions;
        }
        $options['data-key'] = is_array($key) ? json_encode($key) : (string) $key;
        
        return Html::tag('tr', implode('', $cells), $options);
    }
    
    /**
     * 移除数组的查询过滤器
     */
    public function renderFilters()
    {
        if(($model = $this->filterModel) !== null && $model instanceof \yii\base\Model) {
            foreach ($this->columns as $column) {
                if(($column instanceof \yii\grid\DataColumn) && $column->attribute !== null && $model->isAttributeActive($column->attribute)){
                    $value = \yii\helpers\Html::getAttributeValue($model, $column->attribute);
                    if(is_array($value)){
                        $column->filter = false;
                    }
                }
            }
        }
        
        return parent::renderFilters();
    }
}