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
            if(!empty($apis['force'])){ // 开启强一致性判断
                $transaction = Yii::$app->db->beginTransaction(); // 使用事务关联
                $isError = false;
            }
            foreach($apis as $route=>$params){
                if($this->module){
                    $route = trim($this->module,'/').'/'.$route;
                }
                
                $routeKey = str_replace(["/",".","?"],$this->space_str,$route);
                try{
                    if(($re = $this->runApi($route,$params)) !== false){
                        if(isset($re['code']) && $re['code']!='0'){
                            $isError = true;
                        }
                        $result[$routeKey] = $re;
                    }
                }catch (\yii\base\Exception $exception){var_dump($apis);exit;
                    $isError = true;
                    $result[$routeKey] = [
                        'name' => ($exception instanceof \yii\base\Exception || $exception instanceof \yii\base\ErrorException) ? $exception->getName() : 'Exception',
                        'msg' => $exception->getMessage(),
                        'code' => $exception->getCode(),
                    ];
                }
            }
            
            // 一致性处理
            if(!empty($apis['force'])){ // 开启强一致性判断
                if($isError){
                    $transaction->rollBack();
                    if(is_array($result)){
                        foreach($result as $routeKey=>$routeVal){
                            if(isset($routeVal['code']) && $routeVal['code']=='0'){
                                $result[$routeKey] = [
                                    'name' => 'Force',
                                    'msg' => "事务回滚",
                                    'code' => "500",
                                ];
                            }
                        }
                    }
                }else{
                    $transaction->commit(); // 提交事务
                }
            }
        }
        return $result;
    }
    
    /**
     * 执行API，接口地址，参数
     */
    protected function runApi($route,$params){
        $route = str_replace("?","",$route);
        $controller = Yii::$app->createController($route);
        if(empty($controller) || !$controller[0]->createAction($controller[1])) return false;
        
        // 处理请求参数
        $oldQueryParams = Yii::$app->request->getQueryParams();
        $oldBodyParams = Yii::$app->request->getBodyParams();
        if($params){
            if(is_string($params)){
                try{
                    $params = \yii\helpers\Json::decode($params);
                }catch (\yii\base\InvalidArgumentException $e){
                    parse_str($params, $params);
                }
            }
            
            if(is_array($params)){
                Yii::$app->request->setQueryParams(array_merge($oldQueryParams,$params));
                Yii::$app->request->setBodyParams(array_merge($oldBodyParams,$params));
                foreach($params as $key=>$value){
                    $_REQUEST[$key] = $_POST[$key] = $_GET[$key] = $value;
                }                
            }
        }
        
        $result = Yii::$app->runAction($route, $params);
        if ($result instanceof \yii\web\Response){
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

