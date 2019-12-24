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
		
		// 判断是否安装
		if(!file_exists(Yii::getAlias('@runtime/webadmin-install.lock'))){
		    Yii::$app->getResponse()->redirect(\yii\helpers\Url::toRoute('/config/install'));
		}
        
		// 初始化模块
        \webadmin\modules\config\models\SysModules::initModule();

		// 脚手架
		if (!YII_ENV_PROD) {
		    if(Yii::$app->hasModule('gii')){
		        $modules = Yii::$app->getModules();
				Yii::$app->setModule('gii', [
					'class' => 'yii\gii\Module',
					'generators' => [
						'model' => [
							'class' => '\webadmin\generators\model\Generator',
						    'templates'=> (isset($modules['gii']['generators']['model']['templates'])
						        ? $modules['gii']['generators']['model']['templates'] 
						        : []),
						],
						'crud' => [
							'class' => '\webadmin\generators\crud\Generator',
						    'templates'=> (isset($modules['gii']['generators']['crud']['templates'])
						        ? $modules['gii']['generators']['crud']['templates']
						        : []),
						],
						'module' => [
							'class' => '\webadmin\generators\module\Generator',
						    'templates'=> (isset($modules['gii']['generators']['module']['templates'])
						        ? $modules['gii']['generators']['module']['templates']
						        : []),
						],
					],
				]);
			}
		}

		// 用户组件
		Yii::$app->setComponents([
			'user' => [
				'class' => '\yii\web\User',
				'identityClass' => '\webadmin\modules\authority\models\AuthUser',
				'enableAutoLogin' => true,
				'enableSession' => true,
				'loginUrl'=>['/authority/user/login'],
			],
		]);

    }
}
