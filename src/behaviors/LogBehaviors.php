<?php
/**
 * 记录日志行为
 */
namespace webadmin\behaviors;

use Yii;
use yii\base\ActionEvent;
use yii\web\Controller;

defined('YII_BEGIN_TIME') or define('YII_BEGIN_TIME', microtime(true));

class LogBehaviors extends \yii\base\Behavior
{    
    
    /**
     * 行为触发的事件
     */
    public function events()
    {
        return [Controller::EVENT_AFTER_ACTION => 'afterAction'];
    }
    
    /**
     * 记录日志
     */
    public function afterAction($event)
    {
        if(Yii::$app->request->getHeaders()->get('X-Pjax') && Yii::$app->response->statusCode=='200'){
            // Pjax模式下Widgets最大最小化修复
            Yii::$app->response->content .= '<script>InitiateWidgets && InitiateWidgets();</script>';
        }
        
        // 记录数据库操作日志
        \webadmin\modules\logs\models\LogDatabase::logmodel()->saveLog();
        
        // 记录操作日志
        if(Yii::$app->user->id){
            $data = ['_GET'=>Yii::$app->request->get(),'_POST'=>Yii::$app->request->post()];
            $controller = Yii::$app->controller;
            if(empty($data['_GET'])) unset($data['_GET']);
            if(empty($data['_POST'])) unset($data['_POST']);
            \webadmin\modules\logs\models\LogUserAction::insertion([
                'remark' => (property_exists($controller,'currNav') ? ($controller->currNav&&is_array($controller->currNav) ? implode('-',$controller->currNav) : '') : ''),
                'action' => (!($controller->module instanceof \yii\base\Application) ? $controller->module->id.'/' : '').$controller->id.'/'.$controller->action->id,
                'request' => ($data ? print_r($data,true) : ""),
                'addtime' => date('Y-m-d H:i:s', floor(YII_BEGIN_TIME)),
                'endtime' => date('Y-m-d H:i:s'),
                'run_millisec' => round((microtime(true) - YII_BEGIN_TIME)*1000),
                'ip' => Yii::$app->request->userIP.(isset($_SERVER['REMOTE_PORT']) ? ':'.$_SERVER['REMOTE_PORT'] : ''),
                'user_id' => (Yii::$app->user->id ? Yii::$app->user->id : 0),
            ]);
        }
    }
}


