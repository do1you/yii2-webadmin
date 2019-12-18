<?php
/**
 * 继承模型，所有模型的父类
 * @author tongyi
 *
 */
namespace webadmin;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Schema;

class ModelCAR extends \yii\db\ActiveRecord
{
    //是否记录数据库日志
    protected $isSaveLog = false;
    
	// 返回静态资源类
    public static function model()
	{
	    return Yii::createObject(get_called_class());
	}
	
	// 保存后动作
	public function afterSave($insert, $changedAttributes)
	{
	    if($this->isSaveLog===true){
	        $className = get_called_class();
	        \webadmin\modules\logs\models\LogDatabase::logmodel()->logs[] = [$className::tableName(),($insert ? 'insert' : 'update'),$changedAttributes,$this->attributes,$this->primaryKey];
	    }
	    return parent::afterSave($insert, $changedAttributes);
	}
	
	// 保存后动作
	public function afterDelete()
	{
	    if($this->isSaveLog===true){
	        $className = get_called_class();
	        \webadmin\modules\logs\models\LogDatabase::logmodel()->logs[] = [$className::tableName(),'delete',$this->attributes,[],$this->primaryKey];
	    }
	    return parent::afterDelete();
	}
	
	// 采用缓存读取信息
	public function getCache($key=null,$params=[])
	{
	    if(empty($key)) return null;
	    if(!method_exists($this,$key) && !property_exists($this,$key)) return null;
	    
	    $cachekey = 'modelCacheData/'.get_called_class().'/'.$key.'/'.md5(serialize($params));
	    $result = Yii::$app->cache->get($cachekey);
	    if($result===false){
	        if(method_exists($this,$key)){
	            $result = call_user_func_array([$this,$key],$params);
	        }else{
	            $result = $this[$key];
	        }
	        
	        Yii::$app->cache->set($cachekey,$result,86400);
	    }
	    
	    return $result;
	}
	
	// 快速插入
	public static function insertion($data = [])
	{
	    $model = static::model();
	    if($model->load($data,'') && $model->save(false)){
	        return $model->primaryKey;
	    }
	    return false;
	}
	
	// AJAX校验模型
	public function ajaxValidation()
	{
	    if(($attribute = Yii::$app->request->getBodyParam('ajaxValidation',Yii::$app->request->getQueryParam('ajaxValidation')))){
	        $attribute = str_replace(array('[]','][','[',']',' '),array('','_','','','_'),substr($attribute,strpos($attribute,'[')));
	        $this->validate($attribute);
	        $result = [];
	        foreach($this->getErrors() as $att => $errors) {
	            $result[$att] = $errors;
	        }
	        
	        if(!empty($result[$attribute])){
	            $result = ['valid'=>false,'message'=>is_array($result[$attribute]) ? implode('<br>',$result[$attribute]) : $result[$attribute]];
	        }else{
	            $result = ['valid'=>true];
	        }
	        Yii::$app->response->data = $result;
	        Yii::$app->end();
	    }
	    return true;
	}
	
	/**
	 * 模型查询
	 * @return \webadmin\ActiveDataProvider
	 */
	public function search($params, $wheres=null, $with=[], $joinWith=[])
	{
	    $this->load($params);
	    
	    $query = static::find();
	    
	    if($this->attributes){
	        $columns = \yii\helpers\ArrayHelper::map($this->getTableSchema()->columns, 'name', 'type');
	        foreach($this->attributes as $key=>$value){
	            $value = is_array($value) ? $value : trim($value);
	            if(is_array($value) || strlen($value)>0){
	                $type = isset($columns[$key]) ? $columns[$key] : '';
	                switch ($type) {
	                    case Schema::TYPE_TINYINT:
	                    case Schema::TYPE_SMALLINT:
	                    case Schema::TYPE_INTEGER:
	                    case Schema::TYPE_BIGINT:
	                    case Schema::TYPE_BOOLEAN:
	                    case Schema::TYPE_FLOAT:
	                    case Schema::TYPE_DOUBLE:
	                    case Schema::TYPE_DECIMAL:
	                    case Schema::TYPE_MONEY:
	                    case Schema::TYPE_DATE:
	                    case Schema::TYPE_TIME:
	                    case Schema::TYPE_DATETIME:
	                    case Schema::TYPE_TIMESTAMP:
	                        $query->andFilterWhere([$key=>$value]);
	                        break;
	                    default:
	                        $likeKeyword = static::getDb()->driverName === 'pgsql' ? 'ilike' : 'like';
	                        $query->andFilterWhere([$likeKeyword,static::tableName().'.'.$key,$value]);
	                        break;
	                }
	            }
	        }
	    }
	    
	    if($wheres!==null){
	        // 自定义查询
	        if(isset($wheres[0]) && is_array($wheres[0])){
	            foreach($wheres as $item){
	                $query->andFilterWhere($item);
	            }
	        }else{
	            $query->andFilterWhere($wheres);
	        }
	    }
	    
	    !empty($with) && $query->with($with);
	    !empty($joinWith) && $query->joinWith($joinWith);
	    
	    $sorts = static::primaryKey();
	    $sorts = !empty($sorts[0]) ? [
	        'defaultOrder' => [
	            $sorts[0] => SORT_DESC, // 默认排序
	        ]
	    ] : [];
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => $sorts,
        ]);

        return $dataProvider;
    }
    
    /**
     * 返回this
     */
    public function getV_self(){
        return $this;
    }
}