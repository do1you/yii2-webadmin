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

		// 加载默认模块
		Yii::$app->setModules([
			'authority' => 'webadmin\modules\authority\Module',
			'logs' => 'webadmin\modules\logs\Module',
			'daemon' => 'webadmin\modules\daemon\Module',
		]);
        
		// 初始化模块
        \webadmin\modules\config\models\SysModules::initModule();
    }
}
