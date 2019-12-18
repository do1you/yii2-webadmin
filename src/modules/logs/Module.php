<?php
/**
 * logs module definition class
 */
 
namespace webadmin\modules\logs;

use Yii;

class Module extends \webadmin\Module
{
    /**
     * 控制器执行命名空间
     */
    public $controllerNamespace = 'webadmin\modules\logs\controllers';

    /**
     * 模块初始化
     */
    public function init()
    {
        parent::init();

        // 控制台命令
        if(Yii::$app instanceof \yii\console\Application){
            $this->controllerNamespace = 'webadmin\modules\logs\console';
        }
    }
}
