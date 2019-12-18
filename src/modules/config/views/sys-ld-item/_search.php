<?php

use yii\helpers\Html;
use webadmin\widgets\ActiveForm;

?>

<div class="row sys-ld-item-search">
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

				<?= $form->field($model, 'ident')->searchInput() ?>

				<?= $form->field($model, 'state')->searchInput()->dropDownList($model->getV_state(false),['prompt'=>'请选择']) ?>

                <div class="form-group">
                    <?= Html::submitButton(Yii::t('common','查询'), ['class' => 'btn btn-primary']) ?>
                </div>

				<?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
</div>
