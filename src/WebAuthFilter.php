<?php
/**
 * 判断权限是否允许执行
 */
namespace webadmin;

use Yii;
use yii\base\ActionEvent;
use yii\web\Controller;

class WebAuthFilter extends \yii\base\Behavior
{
    public $isAccessToken = true;
    
    
    /**
     * 行为触发的事件
     */
    public function events()
    {
        return [Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
    }
    
    /**
     * 判断权限
     */
    public function beforeAction($event)
    {
        // 不用处理权限
        if(!$this->isAccessToken){
            return true;
        }
        
        // 未登录跳转到登录页
        if(Yii::$app->user->isGuest){
            Yii::$app->user->loginRequired();
        }
        
        // 开启系统调试模式或者用户调试模式
        if((defined('YII_DEBUG')&&YII_DEBUG) || (defined('DEBUG_USER')&&DEBUG_USER)){
            return true;
        }
        
        // 内置超管直接通过
        if(Yii::$app->user->id=='1'){
            return true;
        }
        
        $module = !(Yii::$app->controller->module instanceof \yii\base\Application) ? Yii::$app->controller->module->id : '';
        $moduleAct = ($module ? $module.'/' : '').Yii::$app->controller->id;
        $controller = ($module ? $module.'/' : '').Yii::$app->controller->id.'/'.lcfirst(Yii::$app->controller->action->id);
        list($pathInfo) = explode('.',trim(Yii::$app->request->pathInfo,'/'));
        
        // 不受控制的方法调整为总是允许访问
        $authUrls = (new \webadmin\modules\authority\models\AuthAuthority)->getCache('authorityUrl',[Yii::$app->user->id]);
        if(!in_array($pathInfo,$authUrls) && !in_array($controller,$authUrls) && !in_array($moduleAct,$authUrls) && !in_array($module,$authUrls)){
            return true;
        }
        
        // 用户权限判断
        $auths = Yii::$app->user->identity ? Yii::$app->user->identity->getCache('getAuthorithUrl',[Yii::$app->user->id]) : [];
        $auths = is_array($auths) ? $auths : [];
        if(in_array($pathInfo,$auths) || in_array($controller,$auths) || in_array($moduleAct,$auths) || in_array($module,$auths)){
            return true;
        }else{
            throw new \yii\web\HttpException(200,Yii::t('authority','您没有权限访问！'));
        }
    }
}

