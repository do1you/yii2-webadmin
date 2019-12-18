<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use webadmin\widgets\ActiveForm;

?>
<?php Pjax::begin(); ?>
<div class="row">
	<div class="col-xs-12">
		<div class="pull-right inline">
			<?php /* <a class="btn btn-primary" href="<?php echo Url::to(['tree']);?>"><i class='fa fa-sitemap'></i> <?= Yii::t('common', '树型数据')?></a> */?>
			<a class="btn btn-primary" href="<?php echo Url::to(['index'])?>"><i class="ace-icon glyphicon glyphicon-list bigger-110"></i> <?php echo Yii::t('common','列表')?></a>
			<?php if(Yii::$app->controller->action->id=='view'):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['update','id'=>$model->primaryKey])?>"><i class="ace-icon fa fa-edit bigger-110"></i> <?php echo Yii::t('common','编辑')?></a>
			<?php endif;?>
		</div>
	</div>
</div>

<div class="form-title"></div>

<div class="row sys-modules-form">
		<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
            <?php $form = ActiveForm::begin(); ?>
            
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'code')->textInput(['maxlength' => true, 'disabled' => 'disabled', 'readonly' => 'readonly']) ?>

            <?= $form->field($model, 'state')->dropDownList($model->getV_state(false),['disabled' => 'disabled', 'readonly' => 'readonly']) ?>

            <?= $form->field($model, 'addtime')->textInput(['disabled' => 'disabled', 'readonly' => 'readonly']) ?>

            <?= $form->field($model, 'is_system')->dropDownList($model->getV_is_system(false),['disabled' => 'disabled', 'readonly' => 'readonly']) ?>
            
            <?= $form->field($model, 'memo')->textarea(['rows' => 4]) ?>


            <?php if(Yii::$app->controller->action->id!='view'):?>
                <div class="form-group">
                	<div class="col-sm-offset-2 col-sm-10">
                    	<?= Html::submitButton(Yii::t('common', '保存'), ['class' => 'btn btn-primary shiny']) ?>
                    </div>
                </div>
            <?php endif;?>
        
            <?php ActiveForm::end(); ?>
	</div>
</div>
<?php Pjax::end(); ?>

