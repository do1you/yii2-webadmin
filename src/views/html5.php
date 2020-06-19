<?php $controller = \Yii::$app->controller;?>
<?php $this->beginPage() ?>
<!doctype html>
<?php if(\webadmin\ext\Helpfn::is_mobile()):?><html lang="<?php echo Yii::$app->charset?>"><?php else:?><html xmlns="http://www.w3.org/1999/xhtml"><?php endif;?>
<head>
	<meta charset="<?php echo Yii::$app->charset?>">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta http-equiv="content-type" content="text/html; charset=<?php echo Yii::$app->charset?>">
	<?php if(property_exists($controller,'keywords')):?><meta name="keywords" content="<?php echo $controller->keywords?>" /><?php endif;?>
	<?php if(property_exists($controller,'description')):?><meta name="description" content="<?php echo $controller->description?>" /><?php endif;?>
	<title><?php echo (property_exists($controller,'pageTitle')&&$controller->pageTitle ? $controller->pageTitle : Yii::$app->name)?></title>
	<?= \yii\helpers\Html::csrfMetaTags() ?>
	<?php if(\webadmin\ext\Helpfn::is_mobile()):?>
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
		<meta content="telephone=no, address=no" name="format-detection">
		<meta name="apple-mobile-web-app-capable" content="yes">
		<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
		<meta name="renderer" content="webkit">
		<meta http-equiv="Cache-Control" content="no-siteapp"/>
		<meta content="no-cache,must-revalidate" http-equiv="Cache-Control">
		<meta content="no-cache" http-equiv="pragma">
		<meta content="0" http-equiv="expires">
	<?php else:?>
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<?php if(file_exists(Yii::getAlias('@web/favicon.ico'))):?>
			<link rel="shortcut icon" href="<?php echo \yii\helpers\Url::to('@web/favicon.ico')?>" type="image/x-icon">
		<?php endif;?>
		<link id="bootstrap-rtl-link" href="" rel="stylesheet" />
		<link id="skin-link" href="" rel="stylesheet" type="text/css" />
	<?php endif;?>
	<?php $this->head() ?>
</head>
<body<?php echo property_exists($controller,'body_class')&&$controller->body_class ? ' class="'.$controller->body_class.'"' : '';?>>
    <?php $this->beginBody() ?>
    <?php echo $content?>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>

