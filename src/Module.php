<?php

namespace webadmin;

/**
 * authority module definition class
 */
class Module extends \yii\base\Module
{
    /**
     * {@inheritdoc}
     */
    public $controllerNamespace = 'webadmin\controllers';

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // 控制台命令
        if((\Yii::$app instanceof \yii\console\Application) || (!empty($_GET['dev']) && $_GET['dev']=='consoledev')){
            $this->controllerNamespace = str_replace('controllers','console',$this->controllerNamespace);
        }
        
        parent::init();
    }
}
