<?php

use yii\helpers\Html;
use webadmin\widgets\ActiveForm;

?>

<div class="row auth-authority-search">
	<div class="col-xs-12">
		<div class="widget margin-bottom-20">
			<div class="widget-body bordered-left bordered-themeprimary">

                <?php $form = ActiveForm::begin([
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

				<?= $form->field($model, 'name')->searchInput() ?>

				<?= $form->field($model, 'url')->searchInput() ?>

				<?= $form->field($model, 'parent_id')->searchInput()->select2(\webadmin\modules\authority\models\AuthAuthority::treeOptions(),['prompt'=>'请选择']) ?>
				
				<?= $form->field($model, 'flag')->searchInput()->dropDownList($model->getV_flag(false),['prompt'=>'请选择']) ?>

				<?= $form->field($model, 'can_allowed')->searchInput()->dropDownList($model->getV_can_allowed(false),['prompt'=>'请选择']) ?>

                <div class="form-group">
                    <?= Html::submitButton(Yii::t('common','查询'), ['class' => 'btn btn-primary']) ?>
                </div>

				<?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
</div>
