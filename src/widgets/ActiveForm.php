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
}