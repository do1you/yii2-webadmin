<?php
/**
 * 缓存查询条件和页码
 */
namespace webadmin\behaviors;

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
     * 不进行缓存的主键值 导出等操作
     */
    public $searchNotKeys = ['is_export'];
    
    /**
     * 根据指定参数做缓存
     */
    public $searchParamsKeys = [];
    
    /**
     * 缓存的作用域
     */
    public $cacheKey;
    
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
        
        if(!in_array($act,$this->searchCacheActions)) return true;
        
        // 设置默认查询缓存作用空间
        if(!$this->cacheKey){
            $module = !(Yii::$app->controller->module instanceof \yii\base\Application) ? Yii::$app->controller->module->id.'/' : '';
            $this->cacheKey = Yii::$app->session->id.'/'.$module.Yii::$app->controller->id.'/'.$act;
        }
        
        // 存在旧的缓存查询数据进行合并
        foreach(['_GET','_POST'] as $key){
            $cacheKey = 'searchBehaviors/'.$this->cacheKey.'/'.$key;
            if($this->searchParamsKeys){
                foreach($this->searchParamsKeys as $k){
                    $cacheKey .= '/'.$k.'_'.Yii::$app->request->post($k,Yii::$app->request->get($k));
                }
            }
            $result = Yii::$app->cache->get($cacheKey);
            if($result && is_array($result)){
                foreach($this->searchNotKeys as $k) unset($result[$k]); // 不缓存
                $GLOBALS[$key] = array_merge($result,$GLOBALS[$key]);
                $_REQUEST = array_merge($result,$_REQUEST);
            }
            Yii::$app->cache->set($cacheKey,$GLOBALS[$key],7200);
        }
        
    }
}

