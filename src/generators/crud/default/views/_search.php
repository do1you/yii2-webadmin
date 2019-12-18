<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

echo "<?php\n";
?>

use yii\helpers\Html;
use webadmin\widgets\ActiveForm;

?>

<div class="row <?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-search">
	<div class="col-xs-12">
		<div class="widget margin-bottom-20">
			<div class="widget-body bordered-left bordered-themeprimary">

                <?= "<?php " ?>$form = ActiveForm::begin([
                    'action' => ['index'],
                    'method' => 'get',
                    'enableClientScript' => false,
                    'enableClientValidation' => false,
                    'enableAjaxValidation' => false,
                    'validationStateOn' => false,
                    'options' => [
                        'data-pjax' => 1,
                        'class' => 'form-inline'
                    ],
                ]); ?>

<?php
$count = 0;
foreach ($generator->getColumnNames() as $attribute) {
    echo "\t\t\t\t<?= " . $generator->generateActiveSearchField($attribute) . " ?>\n\n";
}
?>
                <div class="form-group">
                    <?= "<?= " ?>Html::submitButton(Yii::t('common','查询'), ['class' => 'btn btn-primary', 'id'=>'search_btn']) ?>
                    <?= "<?//= " ?>Html::submitButton(Yii::t('common','导出'), ['class' => 'btn btn-primary', 'id'=>'export_btn']) ?>
                    <?= "<?//= " ?>Html::hiddenInput('is_export',''); ?>
                    <?= "<?php " ?> 
                    //$this->registerJs("$('#search_btn,#export_btn').on('click',function(){
                    //    $(this).closest('form').find('input[name=is_export]').val($(this).attr('id')=='export_btn' ? '1' : '');
                    //});");
                    ?>
                </div>

				<?= "<?php " ?>ActiveForm::end(); ?>
			</div>
		</div>
	</div>
</div>
