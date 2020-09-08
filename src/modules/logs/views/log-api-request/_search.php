<?php

use yii\helpers\Html;
use webadmin\widgets\ActiveForm;

?>

<div class="row log-api-request-search">
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

				<?= $form->field($model, 'create_time')->searchInput()->datetimerange(['style'=>'min-width:280px;']) ?>
				
				<?= $form->field($model, 'interface')->searchInput() ?>

				<?= $form->field($model, 'url')->searchInput() ?>
				
				<?= $form->field($model, 'params')->searchInput() ?>
				
				<?= $form->field($model, 'result_msg')->searchInput() ?>
				
                <div class="form-group">
                    <?= Html::submitButton(Yii::t('common','查询'), ['class' => 'btn btn-primary']) ?>
                </div>

				<?php ActiveForm::end(); ?>
			</div>
		</div>
	</div>
</div>
