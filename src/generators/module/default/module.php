<?php
/**
 * This is the template for generating a module class file.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\module\Generator */

$className = $generator->moduleClass;
$pos = strrpos($className, '\\');
$ns = ltrim(substr($className, 0, $pos), '\\');
$className = substr($className, $pos + 1);

echo "<?php\n";
?>
/**
 * <?= $generator->moduleID ?> module definition class
 */
 
namespace <?= $ns ?>;

use Yii;

class <?= $className ?> extends \yii\base\Module
{
    /**
     * 控制器执行命名空间
     */
    public $controllerNamespace = '<?= $generator->getControllerNamespace() ?>';

    /**
     * 模块初始化
     */
    public function init()
    {
        parent::init();

        // 控制台命令
        if(Yii::$app instanceof \yii\console\Application){
            $this->controllerNamespace = '<?= str_replace("controllers","console",$generator->getControllerNamespace()) ?>';
        }
    }
}
