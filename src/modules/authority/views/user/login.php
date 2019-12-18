<?php 
use yii\widgets\ActiveForm;
\webadmin\WebAdminAsset::register($this);
?>
<?php $form=ActiveForm::begin([
    'id' => 'login-form',
    'enableClientScript' => false,
]); ?>
	<input type="password" style="position:absolute;top:-9999px;"/>
    <div class="login-container animated fadeInDown">
        <div class="loginbox bg-white">
            <div class="loginbox-title"><?php echo Yii::$app->name?></div>
            <div class="loginbox-social">
                <div class="social-title ">加油！骚年，前方的路在等你</div>
            </div>
            <div class="loginbox-or">
                <div class="or-line"></div>
                <div class="or">登录</div>
            </div>
            <div class="loginbox-textbox" style="padding-bottom:0;">
            	<?php echo $this->render('@webadmin/views/_flash'); ?>
            </div>
            <div class="loginbox-textbox">
            	<?php echo $form->field($model, 'login_name', ['inputOptions'=>['placeholder'=>'用户名','class'=>'form-control'], 'template'=>"{input}"]);?>
            </div>
            <div class="loginbox-textbox">
                <?php echo $form->field($model, 'password', ['inputOptions'=>['placeholder'=>'密码','class'=>'form-control'], 'template'=>"{input}"])->passwordInput();?>
            </div>
            <?php /*<div class="loginbox-forgot">
                <a href="<?php echo $this->createUrl('forget')?>">忘记密码</a>
            </div>*/?>
            <div class="loginbox-submit">
                <input type="submit" class="btn btn-primary btn-block" value="登录">
            </div>
            <div class="loginbox-signup"><h5>建议使用以下浏览器</h5></div>
			<div class="text-center">
				<a href="http://www.google.cn/intl/zh-CN/chrome/" target="_blank">Google Chrome</a>&nbsp;&nbsp;&nbsp;
	            <a href="https://www.mozilla.org/en-US/firefox/all/" target="_blank">Firefox</a>&nbsp;&nbsp;&nbsp;
	            <a href="http://www.apple.com/safari/" target="_blank">Safari</a>
			</div>
        </div>
    </div>
<?php ActiveForm::end(); ?>