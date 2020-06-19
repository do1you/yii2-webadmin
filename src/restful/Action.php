<?php
/**
 * 基于rest接口请求封装通用的控制器方法父类
 * 目前是用继承YII2内置的rest服务进行自由的接口业务封包
 */
namespace webadmin\restful;

use Yii;

class Action extends \yii\rest\Action
{
    /**
     * 指定查询的对象模型输出过滤字段
     */
    public $fields;
    
    /**
     * 指定查询的对象模型输出扩展字段
     */
    public $expand;
    
    /**
     * 指定查询模型对象的列表的方法
     * @var String or Array
     */
    public $findAllModel;
    
    /**
     * restful接口初始化
     */
    public function init()
    {
        if($this->fields || $this->expand){
            $serializer = Yii::createObject($this->controller->serializer);
            if($this->fields) $_GET[$serializer->fieldsParam] = $this->fields;
            if($this->expand) $_GET[$serializer->expandParam] = $this->expand;
            Yii::$app->request->setQueryParams($_GET);
        }
        
        parent::init();
    }
    
    /**
     * 根据主键或指定信息查询模型对象信息
     * @var Model
     */
    public function findModel($id)
    {
        if ($this->findModel !== null) {
            return call_user_func($this->findModel, $id, $this);
        }
        
        $modelClass = $this->modelClass;
        $keys = $modelClass::primaryKey();
        if (count($keys) > 1) {
            $values = explode(',', $id);
            if (count($keys) === count($values)) {
                $model = $modelClass::findOne(array_combine($keys, $values));
            }
        } elseif ($id !== null) {
            $model = $modelClass::findOne($id);
        }
        
        if (isset($model)) {
            return $model;
        }
        
        throw new \yii\web\NotFoundHttpException(Yii::t('common', '查询的模型对象信息不存在.'));
    }
    
    
    /**
     * 根据主键数组信息询模型对象列表
     * @var Model Array
     */
    public function findAllModel($ids)
    {
        $ids = is_string($ids) ? [$ids] : $ids;
        if ($this->findAllModel !== null) {
            return call_user_func($this->findAllModel, $ids, $this);
        }
        
        $modelClass = $this->modelClass;
        $keys = $modelClass::primaryKey();
        if (count($keys) > 1) {
            throw new \yii\web\NotFoundHttpException(Yii::t('common', '联合主键不支持进行模型对象主键查询.'));
        }elseif(count($ids)>0) {
            $models = $modelClass::findAll($ids);
        }
        
        if (isset($models)) {
            return $models;
        }
        
        throw new \yii\web\NotFoundHttpException(Yii::t('common', '查询的模型对象信息不存在.'));
    }
}