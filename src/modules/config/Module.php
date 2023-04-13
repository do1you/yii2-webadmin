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
        
        // 加入socket相关的类
        if(!isset(\Yii::$aliases['@Workerman'])){
            \Yii::$app->setAliases([
                '@Workerman' => '@webadmin/socket/workerman',
                '@GatewayWorker' => '@webadmin/socket/GatewayWorker/src',
                '@GatewayClient' => '@webadmin/socket/GatewayClient',
            ]);
        }

        // 加入几个基本的Module
        if(!\Yii::$app->hasModule('authority')){
            \Yii::$app->setModules([
                //'config' => 'webadmin\modules\config\Module',
                'authority' => 'webadmin\modules\authority\Module',
                'logs' => 'webadmin\modules\logs\Module',
                'daemon' => 'webadmin\modules\daemon\Module',
            ]);
        }

		// 控制台命令
        if(Yii::$app instanceof \yii\console\Application){
            $this->controllerNamespace = 'webadmin\modules\config\console';
        }
    }
}
