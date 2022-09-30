<?php
use yii\helpers\Html;
use webadmin\widgets\ActiveForm;
?>

<div class="row">
		<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
            <?php $form = ActiveForm::begin(); ?>
        
            <?= $form->field($model, 'login_name')->textInput(['maxlength' => true, 'disabled' => 'disabled', 'readonly' => 'readonly']) ?>
            
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                
            <?= $form->field($model, 'mobile')->textInput(['maxlength' => true]) ?>
            
            <?= $form->field($model, 'sso_id', [
                'template' => "{label}\n<div class='col-sm-10'><div class='input-group'>{input}<span class='input-group-btn'><button class='btn btn-primary shiny' id='unbind_btn' type='button'>解除绑定</button></span></div>\n{hint}\n{error}</div>",
            ])->textInput(['maxlength' => true, 'disabled' => 'disabled', 'readonly' => 'readonly']) ?>
            
            <?= $form->field($model, 'note')->textarea(['maxlength' => true]) ?>
        
            <div class="form-group">
            	<div class="col-sm-offset-2 col-sm-10">
                	<?= Html::submitButton(Yii::t('authority', '  保  存  '), ['class' => 'btn btn-primary shiny']) ?>
                </div>
            </div>
        
        	<?= $form->field($model, 'sso_id', ['template' => '{input}'])->hiddenInput() ?>
            <?php ActiveForm::end(); ?>
	</div>
</div>

<?php $this->registerJs("
$('#unbind_btn').on('click', function(){
    $('input[name=\"AuthUser[sso_id]\"]').val('');
});
");?>