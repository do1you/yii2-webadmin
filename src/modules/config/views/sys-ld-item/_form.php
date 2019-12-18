<?php

use yii\helpers\Html;
use yii\helpers\Url;
use webadmin\widgets\ActiveForm;
$ddModel = $model->parent_id>0 ? $model['topParent'] : null;
?>
<div class="row">
	<div class="col-xs-12">
		<div class="pull-right inline">
			<?php if($model->parent_id>0): // 选项?>
				<a class="btn btn-primary" href="<?php echo Url::to(['index','id'=>$ddModel['id']])?>"><i class="ace-icon glyphicon glyphicon-list bigger-110"></i> <?php echo Yii::t('common','列表')?></a>
    			<?php if(Yii::$app->controller->action->id!='create'):?>
    				<a class="btn btn-primary" href="<?php echo Url::to(['create','id'=>$ddModel['id']])?>"><i class="ace-icon fa fa-plus bigger-110"></i> <?php echo Yii::t('common','添加')?></a>
    			<?php endif;?>
    			<?php if(Yii::$app->controller->action->id=='view'):?>
    				<a class="btn btn-primary" href="<?php echo Url::to(['update','id'=>$model->primaryKey])?>"><i class="ace-icon fa fa-edit bigger-110"></i> <?php echo Yii::t('common','编辑')?></a>
    			<?php endif;?>
    			<?php if(Yii::$app->controller->action->id=='view' || Yii::$app->controller->action->id=='update'):?>
    				<a class="btn btn-primary" href="<?php echo Url::to(['delete','id'=>$model->primaryKey])?>" data-pjax="0"><i class="ace-icon fa fa-trash-o bigger-110"></i> <?php echo Yii::t('common','删除')?></a>
    			<?php endif;?>
			<?php else:?>
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
			<?php endif;?>
			
		</div>
	</div>
</div>

<div class="form-title"></div>

<div class="row sys-ld-item-form">
		<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
            <?php $form = ActiveForm::begin(); ?>
            <?php if($model->parent_id>0): // 选项?>
            	<?php 
            	$dataList = [$ddModel['id']=>$ddModel['name']] + \webadmin\modules\config\models\SysLdItem::treeOptions($ddModel['id'],[],0,true,true);
            	unset($dataList[$model['id']]);
            	?>
            	<?= $form->field($model, 'parent_id')->select2($dataList,[]) ?>
            	<?php if(Yii::$app->controller->action->id=='create'):?>
            		<?= $form->field($model, 'value')->hint(Yii::t('config','一行一个选项，用“|”分隔选项值和选项名称'))->textarea(['rows' => 8, 'maxlength' => false]) ?>
            	<?php else:?>
            		<?= $form->field($model, 'value')->textInput(['maxlength' => true]) ?>
            		<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            	<?php endif;?>
            	<?= $form->field($model, 'reorder')->textInput() ?>
            <?php else: // 字典?>
            	<?= $form->field($model, 'ident')->textInput(['maxlength' => true]) ?>
            	<?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
            <?php endif;?>
            
            <?= $form->field($model, 'state')->dropDownList($model->getV_state(false),[]) ?>

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

