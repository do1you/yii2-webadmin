<?php
use webadmin\widgets\ActiveForm;

$form = ActiveForm::begin(['enableClientScript'=>false]);
$model = new \webadmin\modules\authority\models\AuthUser();
?>
<div class="row">
	<div class="col-lg-6 col-sm-6 col-xs-12">
        <div class="well with-header">
            <div class="header bordered-themeprimary">普通文本框</div>
            <?= $form->field($model, 'name')->label('测试框')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">自定格式文本</div>
            <?= $form->field($model, 'name')->label('测试框')->mask('aa-9999米',['maxlength' => true]) ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">多行文本框</div>
            <?= $form->field($model, 'name')->label('测试框')->textArea(['rows' => 3]) ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">开关按纽</div>
            <?= $form->field($model, 'name')->label('测试框')->switchs([]) ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">复选框/单选框</div>
            <?= $form->field($model, 'name')->label('测试框')->checkbox([]) ?>
            <?= $form->field($model, 'name')->label('测试框')->checkboxList(\webadmin\modules\config\models\SysLdItem::dd('enum'),[]) ?>
            <?= $form->field($model, 'name')->label('测试框')->radioList(\webadmin\modules\config\models\SysLdItem::dd('enum'),[]) ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">下拉框</div>
            <?= $form->field($model, 'name')->label('测试框')->dropDownList(\webadmin\modules\config\models\SysLdItem::dd('enum'),['prompt'=>'请选择']) ?>
            <?= $form->field($model, 'access_token')->label('测试框')->select2(\webadmin\modules\config\models\SysLdItem::dd('enum'),[]) ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">下拉多选框</div>
            <?= $form->field($model, 'mobile')->label('测试框')->duallistbox(\webadmin\modules\config\models\SysLdItem::dd('enum'),[]) ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">异步单选</div>
            <?= $form->field($model, 'login_name')->label('测试框')->selectajax(\yii\helpers\Url::toRoute('/config/default/sys-region'),[]) ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">异步多选</div>
            <?= $form->field($model, 'state')->label('测试框')->selectajaxmult(\yii\helpers\Url::toRoute('/config/default/sys-region'),[]) ?>
        </div>
	</div>
	<div class="col-lg-6 col-sm-6 col-xs-12">
		<div class="well with-header">
            <div class="header bordered-themeprimary">首字母拼音</div>
            <?= $form->field($model, 'password_curr')->label('测试框')->pinyin('#authuser-password', []) ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">日期选择</div>
            <?= $form->field($model, 'password')->label('测试框')->date([]) ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">时间选择</div>
            <?= $form->field($model, 'id')->label('测试框')->time([]) ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">日期时间</div>
            <?= $form->field($model, 'note')->label('测试框')->datetime([]) ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">日期范围</div>
            <?= $form->field($model, 'password_confirm')->label('测试框')->daterange([]) ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">日期时间范围</div>
            <?= $form->field($model, 'access_token')->label('测试框')->datetimerange([]) ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">树选择控件</div>
            <?= $form->field($model, 'old_password')->label('测试框')->treeList(\webadmin\modules\authority\models\AuthAuthority::treeData("0"),['title'=>Yii::t('authority','请给该角色分配需要操作的权限')]) ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">单文件上传</div>
            <?= $form->field($model, 'old_password')->label('测试框')->oneFile() ?>
        </div>
        <div class="well with-header">
            <div class="header bordered-themeprimary">多文件上传</div>
            <?= $form->field($model, 'old_password')->label('测试框')->manyFile() ?>
        </div>
	</div>
</div>
<?php ActiveForm::end(); ?>
ddmulti
mask
