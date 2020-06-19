<?php
/**
 * 基于rest接口请求封装的通用的多API组装请求方法
 */
namespace webadmin\restful;

use Yii;

class MultiApiAction extends \webadmin\restful\Action
{
    /**
     * 输出路由的间隔符号
     */
    public $space_str = '_';
    
    /**
     * 路由前缀
     */
    public $module = '';
    
    /**
     * restful接口初始化
     */
    public function init()
    {
        
    }
    
    /**
     * 同时执行多个API接口的方法
     * @return Array
     */
    public function run()
    {
        $apis = Yii::$app->request->getBodyParams();
        $apis = $apis ? $apis : Yii::$app->request->getQueryParams();
        $result = [];
        if(!empty($apis) && is_array($apis)){
            foreach($apis as $route=>$params){
                if($this->module){
                    $route = trim($this->module,'/').'/'.$route;
                }
                
                if(stripos($route,'/')!==false && ($re = $this->runApi($route,$params)) !== false){
                    $result[str_replace(array("/","."),$this->space_str,$route)] = $re;
                }
            }
        }
        return $result;
    }
    
    /**
     * 执行API，接口地址，参数
     */
    protected function runApi($route,$params){
        $controller = Yii::$app->createController($route);
        if(empty($controller) || !$controller[0]->createAction($controller[1])) return false;
        
        // 处理请求参数
        $oldQueryParams = Yii::$app->request->getQueryParams();
        $oldBodyParams = Yii::$app->request->getBodyParams();
        if($params){
            if(is_string($params)){
                $params = \yii\helpers\Json::decode($params);
            }
            
            if(is_array($params)){
                Yii::$app->request->setQueryParams(array_merge($oldQueryParams,$params));
                Yii::$app->request->setBodyParams(array_merge($oldBodyParams,$params));
            }
        }
        
        $result = Yii::$app->runAction($route, $params);
        if ($result instanceof Response){
            $response = $result;
        }else{
            $response = Yii::$app->getResponse();
            if ($result !== null) {
                $response->data = $result;
            }
        }
        
        Yii::$app->request->setQueryParams($oldQueryParams);
        Yii::$app->request->setBodyParams($oldBodyParams);
        return $response->data;
    }
}

