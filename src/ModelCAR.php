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
use yii\db\StaleObjectException;

class ModelCAR extends \yii\db\ActiveRecord
{
    //是否记录数据库日志
    protected $isSaveLog = false;
    
	// 返回静态资源类
    public static function model()
	{
	    return Yii::createObject(get_called_class());
	}
	
	// 返回对象的多关键值对应
	public function getModelKey($model, $attributes)
	{
	    $key = [];
	    if(is_array($attributes)){
	        foreach ($attributes as $attribute) {
	            $key[] = $this->normalizeModelKey($model[$attribute]);
	        }
	    }else{
	        $key[] = $this->normalizeModelKey($model[$attributes]);
	    }
	    
	    if (count($key) > 1) {
	        return serialize($key);
	    }
	    $key = reset($key);
	    return is_scalar($key) ? $key : serialize($key);
	}
	
	// 兼容toString的对象处理
	public function normalizeModelKey($value)
	{
	    if (is_object($value) && method_exists($value, '__toString')) {
	        $value = $value->__toString();
	    }
	    
	    return $value;
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
	public function getCache($key=null,$params=[],$time=86400,$f5=false)
	{
	    if(empty($key)) return null;
	    //if(!method_exists($this,$key) && !property_exists($this,$key)) return null;
	    
	    //$cachekey = 'modelCacheData/'.get_called_class().'/'.$key.'/'.md5(serialize($params));
	    $cachekey = 'modelCacheData/'.get_called_class().'/'.$key.'/'.md5(serialize($this)).'/'.md5(serialize($params));
	    $result = Yii::$app->cache->get($cachekey);
	    if($result===false || $f5){
	        if(method_exists($this,$key)){
	            $result = call_user_func_array([$this,$key],$params);
	        }elseif(isset($this[$key])){
	            $result = $this[$key];
	        }else{
	            return null;
	        }
	        
	        Yii::$app->cache->set($cachekey,$result,$time);
	    }
	    
	    return $result;
	}
	
	// 快速插入
	public static function insertion($data = [])
	{
	    $model = static::model();
	    $model->loadDefaultValues();
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
	                $attribute = static::tableName().'.'.$key;
	                if(is_array($value)){
	                    $query->andFilterWhere([$attribute=>$value]);
	                }else{
	                    if(strpos($value, '~')!==false){ // 范围查询
	                        list($start, $end) = explode('~', $value);
	                        $query->andFilterWhere(['>=',$attribute, trim($start)]);
	                        $query->andFilterWhere(['<=',$attribute, trim($end)]);
	                    }elseif(preg_match('/^(<>|!=|>=|>|<=|<|=)/', $value, $matches)){
	                        $operator = $matches[1];
	                        $value = substr($value, strlen($operator));
	                        $query->andFilterWhere([$operator,$attribute, $value]); // 指定操作
	                    }else{
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
	                                $query->andFilterWhere([$attribute=>$value]);
	                                break;
	                            default:
	                                $query->andFilterWhere(['like', $attribute, $value]); // 模糊查询
	                                break;
	                        }
	                    }
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
	            $sorts[0] => SORT_DESC,
	        ],
	    ] : [];
	    $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => $sorts,
            'pagination' => ['pageSizeLimit' => [1, 500]],
        ]);

        return $dataProvider;
    }
    
    /**
     * 返回this
     */
    public function getV_self(){
        return $this;
    }
    
    /**
     * 继承删除关系，用于处理密钥锁
     */
    protected function deleteInternal()
    {
        if (!$this->beforeDelete()) {
            return false;
        }
        
        // we do not check the return value of deleteAll() because it's possible
        // the record is already deleted in the database and thus the method will return 0
        $condition = $this->getOldPrimaryKey(true);
        $lock = $this->optimisticLock();
        if ($lock !== null) {
            $condition[$lock] = $this->$lock;
        }
        
        if($this->hasMethod('getLockAttribute') && $this->hasMethod('getSecretKey') && $this->hasMethod('getOldSecretKey')){
            $secretLock = $this->getLockAttribute();
            $condition[$secretLock] = $this->getOldSecretKey();
            
            if($condition[$secretLock] != $this->getOldAttribute($secretLock)){
                throw new \yii\web\HttpException(200, Yii::t('common', '数据库被篡改，禁止操作!'));
            }
        }
        
        $result = static::deleteAll($condition);
        if (($lock !== null || (isset($secretLock) && $condition[$secretLock] != $secretLock)) && !$result) {
            throw new StaleObjectException('The object being deleted is outdated.');
        }
        $this->setOldAttributes(null);
        $this->afterDelete();
        
        return $result;
    }
    
    /**
     * 继续更新关系，用于处理密钥锁
     */
    protected function updateInternal($attributes = null)
    {
        if (!$this->beforeSave(false)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributes);
        if (empty($values)) {
            $this->afterSave(false, $values);
            return 0;
        }
        $condition = $this->getOldPrimaryKey(true);
        $lock = $this->optimisticLock();
        if ($lock !== null) {
            $values[$lock] = $this->$lock + 1;
            $condition[$lock] = $this->$lock;
        }
        
        if($this->hasMethod('getLockAttribute') && $this->hasMethod('getSecretKey') && $this->hasMethod('getOldSecretKey')){
            $secretLock = $this->getLockAttribute();
            $condition[$secretLock] = $this->getOldSecretKey();
            //var_dump($this->getOldSecretKey());var_dump($this->getSecretCol(true));
            //var_dump($this->getSecretKey());var_dump($this->getSecretCol());
            //exit;
            if($condition[$secretLock] != $this->getOldAttribute($secretLock)){
                throw new \yii\web\HttpException(200, Yii::t('common', '数据库被篡改，禁止操作!'));
            }
        }
        
        // We do not check the return value of updateAll() because it's possible
        // that the UPDATE statement doesn't change anything and thus returns 0.
        $rows = static::updateAll($values, $condition);
        
        if (($lock !== null || (isset($secretLock) && $condition[$secretLock] != $this->$secretLock)) && !$rows) {
            throw new StaleObjectException('The object being updated is outdated.');
        }
        
        if (isset($values[$lock])) {
            $this->$lock = $values[$lock];
        }
        
        $changedAttributes = [];
        $oldAttributes = $this->getOldAttributes();
        foreach ($values as $name => $value) {
            $changedAttributes[$name] = isset($oldAttributes[$name]) ? $oldAttributes[$name] : null;
            $this->setOldAttribute($name, $value);
        }
        $this->afterSave(false, $changedAttributes);
        
        return $rows;
    }
}