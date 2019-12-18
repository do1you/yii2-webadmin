<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use webadmin\widgets\ActiveForm;

if($model->authorityList===null && $model->id)
    $model->authorityList = \yii\helpers\ArrayHelper::map($model->authorithRels,'authority_id','authority_id');
?>
<?php Pjax::begin(); ?>
<div class="row">
	<div class="col-xs-12">
		<div class="pull-right inline">
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

<div class="row auth-role-form">
		<div class="col-lg-offset-3 col-sm-offset-1 col-lg-6 col-sm-10 col-xs-12">
            <?php $form = ActiveForm::begin(); ?>
            
            <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'is_system')->dropDownList($model->getV_is_system(false),['readonly' => 'readonly', 'disabled' => 'disabled']) ?>

			<?= $form->field($model, 'role_group')->dropDownList($model->getV_role_group(false),[]) ?>

            <?= $form->field($model, 'note')->textarea(['rows' => 4]) ?>
             
            <?= $form->field($model, 'authorityList')->treeList(\webadmin\modules\authority\models\AuthAuthority::treeData("0",[],$model->authorityList),['title'=>Yii::t('authority','请给该角色分配需要操作的权限')]) ?>

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

