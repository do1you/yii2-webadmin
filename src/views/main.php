<?php $controller = \Yii::$app->controller;?>
<?php $this->beginPage() ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="language" content="<?php echo Yii::$app->language?>" />
    <?php if(property_exists($controller,'keywords')):?><meta name="keywords" content="<?php echo $controller->keywords?>" /><?php endif;?>
    <?php if(property_exists($controller,'description')):?><meta name="description" content="<?php echo $controller->description?>" /><?php endif;?>
    <title><?php echo (property_exists($controller,'pageTitle') ? $controller->pageTitle : Yii::$app->name)?></title>
    <?= \yii\helpers\Html::csrfMetaTags() ?>
    <meta name="copyright" content="Copyright <?php echo date('Y')?> - <?php echo Yii::$app->name?>" />
    <?php $this->head() ?>
</head>
<body<?php echo property_exists($controller,'body_class')&&$controller->body_class ? ' class="'.$controller->body_class.'"' : '';?>>
    <?php $this->beginBody() ?>
    <?php echo $content?>
    <?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>