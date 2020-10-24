<?php

namespace webadmin\modules\daemon;

use Yii;

/**
 * daemon module definition class
 */
class Module extends \webadmin\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'webadmin\modules\daemon\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        // 控制台命令
        if((\Yii::$app instanceof \yii\console\Application) || (!empty($_GET['dev']) && $_GET['dev']=='console')){
            $this->controllerNamespace = 'webadmin\modules\daemon\console';
        }
    }
}
