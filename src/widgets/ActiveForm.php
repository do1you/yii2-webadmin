<?php
/**
 * 继承系统的表单，根据现有模板输出内容
 */
namespace webadmin\widgets;

use Yii;

class ActiveForm extends \yii\widgets\ActiveForm
{
    public $fieldClass = '\webadmin\widgets\ActiveField';
    
    public $options = ['class'=>'form-horizontal validate'];
    
    public $enableAjaxValidation = true;
    
    /**
     * 输出表单的默认JS脚本
     */
    public function registerClientScript()
    {
        $id = $this->options['id'];
        $view = $this->getView();
        $view->registerJs("$('#{$id}').bootstrapValidator();");
        $view->registerJsFile('@assetUrl/js/validation/bootstrapvalidator.js',['depends' => \webadmin\WebAdminAsset::className()]);
    }
    
    /**
     * 获取查询表单文本框
     */
    public function searchInput($options = [])
    {
        $this->fieldConfig = [
            'template' => '{label}{input}{hint}',
            'options' => ['class' => 'form-group margin-right-10 margin-top-5 margin-bottom-5'],
            'labelOptions' => ['class' => 'control-label padding-right-5'],
        ];
        
        return $this;
    }
}