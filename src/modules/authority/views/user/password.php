<?php
use yii\helpers\Html;
use webadmin\widgets\ActiveForm;
?>

<div class="row">
	<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
        <?php $form = ActiveForm::begin(); ?>
    
        <?= $form->field($model, 'login_name')->textInput(['maxlength' => true, 'disabled' => 'disabled', 'readonly' => 'readonly',]) ?>
        
        <?= $form->field($model, 'old_password')->passwordInput(['maxlength' => true]) ?>
        
        <?= $form->field($model, 'password')->passwordInput(['maxlength' => true]) ?>
        
        <?= $form->field($model, 'password_confirm')->passwordInput(['maxlength' => true]) ?>
        
        <div class="form-group">
        	<div class="col-sm-offset-2 col-sm-10">
            	<?= Html::submitButton(Yii::t('authority', '  保  存  '), ['class' => 'btn btn-primary shiny']) ?>
            </div>
        </div>
    
        <?php ActiveForm::end(); ?>
	</div>
</div>