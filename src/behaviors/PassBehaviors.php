<?php

/**
 * 过于简单的密码判断
 */

namespace webadmin\behaviors;

use Yii;
use yii\web\Controller;

class PassBehaviors extends \yii\base\Behavior
{
    /**
     * 简易密码列表
     */
    public $easyPassword = ['123456','12345678','111111','11111111','888888','123123'];
    
    /**
     * 行为触发的事件
     */
    public function events()
    {
        return [Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
    }
    
    /**
     * 判断简单的密码
     */
    public function beforeAction($event)
    {
        if($event->action->id=='password'){
            return true;
        }
        
        if($this->easyPassword && is_array($this->easyPassword) && !Yii::$app->user->isGuest && Yii::$app->user->identity){
            foreach($this->easyPassword as $pass){
                if(Yii::$app->user->identity->validatePassword($pass)){
                    Yii::$app->session->setFlash('info',Yii::t('authority', '您的密码设置过于简单，请您修改密码！'));
                    Yii::$app->getResponse()->redirect(\yii\helpers\Url::toRoute('/authority/user/password'));
                    Yii::$app->end();
                    break;
                }
                
            }
        }
        
        return true;
    }
}
