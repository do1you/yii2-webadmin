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

		</div>
	</div>
</div>

<div class="form-title"></div>

<div class="row log-imei-form">
		<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
            <?php $form = ActiveForm::begin(); ?>
            
            <?= $form->field($model, 'platform')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'imei')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'user_id')->textInput(['value'=>$model['user']['name']]) ?>

            <?= $form->field($model, 'create_time')->textInput() ?>


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

