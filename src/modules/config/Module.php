<?php

namespace webadmin\modules\config;

use Yii;

/**
 * config module definition class
 */
class Module extends \webadmin\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'webadmin\modules\config\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

		// 控制台命令
        if(Yii::$app instanceof \yii\console\Application){
            $this->controllerNamespace = 'webadmin\modules\config\console';
        }
    }
}
