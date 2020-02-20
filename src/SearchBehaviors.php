<?php
/**
 * 缓存查询条件和页码
 */
namespace webadmin;

use Yii;
use yii\base\ActionEvent;
use yii\web\Controller;

class SearchBehaviors extends \yii\base\Behavior
{
    /**
     * 需要缓存的控制器方法
     */
    public $searchCacheActions = ['index', 'list', 'tree'];
    
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
        $act = $event->action->id;
        $module = !(Yii::$app->controller->module instanceof \yii\base\Application) ? Yii::$app->controller->module->id.'/' : '';
        $cacheKey = 'searchBehaviors/'.Yii::$app->session->id.'/'.$module.Yii::$app->controller->id.'/'.$act;
        
        if(!in_array($act,$this->searchCacheActions)) return true;
        
        // 存在旧的缓存查询数据进行合并
        $result = Yii::$app->cache->get($cacheKey);
        if($result && is_array($result)){
            unset($result['is_export']); // 导出操作不缓存
            $_GET = array_merge(
                $result,
                $_GET
            );
        }
        
        Yii::$app->cache->set($cacheKey,$_GET,7200);
    }
}

