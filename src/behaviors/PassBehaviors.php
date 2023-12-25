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
    public $easyPassword = ['123456', '12345678', '111111', '11111111'];

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
        if (preg_match("/cli/i", php_sapi_name())) {
            return true;
        }
        
        if ($event->action->id == 'logout') {
            return true;
        }

        if ($event->action->id == 'password') {
            if (Yii::$app->request->isPost) {
                unset(Yii::$app->session['API_PASSWORD']);
                unset(Yii::$app->session['RESET_PASSWORD']);
            }
            return true;
        }
        
        // 密码到期时间判断
        if(Yii::$app->user->identity && time() >= strtotime(Yii::$app->user->identity->pass_time)){
            Yii::$app->session->setFlash('info', Yii::t('authority', '您的密码已经到期，请您修改密码！'));
            Yii::$app->getResponse()->redirect(\yii\helpers\Url::toRoute('/authority/user/password'));
            $event->isValid = false;
            Yii::$app->end();
        }

        if (isset(Yii::$app->session['RESET_PASSWORD']) && Yii::$app->session['RESET_PASSWORD'] == 1) {
            Yii::$app->session->setFlash('info', Yii::t('authority', '您当前密码设置过于简单，请您修改密码！'));
            Yii::$app->getResponse()->redirect(\yii\helpers\Url::toRoute('/authority/user/password'));
            $event->isValid = false;
            Yii::$app->end();
        }

        if (Yii::$app->session['API_PASSWORD']) {
            return true;
        }

        Yii::$app->session['API_PASSWORD'] = date('Y-m-d H:i:s');
        if ($this->easyPassword && is_array($this->easyPassword) && !Yii::$app->user->isGuest && Yii::$app->user->identity) {
            foreach ($this->easyPassword as $pass) {
                if (Yii::$app->user->identity->validatePassword($pass)) {
                    Yii::$app->session->setFlash('info', Yii::t('authority', '您的密码设置过于简单，请您修改密码！'));
                    Yii::$app->getResponse()->redirect(\yii\helpers\Url::toRoute('/authority/user/password'));
                    $event->isValid = false;
                    Yii::$app->end();
                    break;
                }
            }
        }

        return true;
    }
}
