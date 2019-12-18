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
			<?php if(Yii::$app->controller->action->id!='create'):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['create'])?>"><i class="ace-icon fa fa-plus bigger-110"></i> <?php echo Yii::t('common','添加')?></a>
			<?php endif;?>
			<?php if(Yii::$app->controller->action->id=='view'):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['update','id'=>$model->primaryKey])?>"><i class="ace-icon fa fa-edit bigger-110"></i> <?php echo Yii::t('common','编辑')?></a>
			<?php endif;?>
			<?php if(Yii::$app->controller->action->id=='view' || Yii::$app->controller->action->id=='update'):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['delete','id'=>$model->primaryKey])?>" data-pjax="0"><i class="ace-icon fa fa-trash-o bigger-110"></i> <?php echo Yii::t('common','删除')?></a>
			<?php endif;?>
		</div>
	</div>
</div>

<div class="form-title"></div>

<div class="row sys-crontab-form">
		<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
            <?php $form = ActiveForm::begin(); ?>
            
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'command')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'crontab_type')->dropDownList($model->getV_crontab_type(false),[]) ?>

            <?= $form->field($model, 'repeat_min')->hint(Yii::t('config','每次间隔多少分钟运行一次计划任务'))->textInput() ?>

            <?= $form->field($model, 'timing_day')->hint(Yii::t('config','每次间隔多少天运行一次定时的计划任务'))->textInput() ?>

            <?= $form->field($model, 'timing_time')->hint(Yii::t('config','指定执行计划任务的时间'))->time() ?>
            
            <?= $form->field($model, 'state')->dropDownList($model->getV_state(false),[]) ?>

			<?php if(Yii::$app->controller->action->id!='create'):?>
                <?= $form->field($model, 'last_time')->textInput(['maxlength' => true, 'value'=>date('Y-m-d H:i:s',$model->last_time), 'disabled'=>'disabled','readonly'=>'readonly']) ?>
    
                <?= $form->field($model, 'run_state')->dropDownList($model->getV_run_state(false),['disabled'=>'disabled','readonly'=>'readonly']) ?>
            <?php endif;?>

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
<?php 
$this->registerJs("$('#syscrontab-crontab_type').on('change',function(){
    var val = $(this).val();
    if(val=='0'){ // 间隔任务
        $('#syscrontab-repeat_min').parents('.form-group').slideDown();
        $('#syscrontab-timing_day,#syscrontab-timing_time').parents('.form-group').slideUp();
    }else{ // 定点任务
        $('#syscrontab-repeat_min').parents('.form-group').slideUp();
        $('#syscrontab-timing_day,#syscrontab-timing_time').parents('.form-group').slideDown();
    }
}).triggerHandler('change');");
?>
<?php Pjax::end(); ?>

