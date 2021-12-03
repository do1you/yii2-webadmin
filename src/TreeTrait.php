<?php
/**
 * 树型模型使用的公用方法
 *
 */
namespace webadmin;

use Yii;

trait TreeTrait
{
    /**
     * 主键标识
     */
    public $col_id = 'id';
    
    /**
     * 上级标识
     */
    public $col_parent = 'parent_id';
    
    /**
     * 名称标识
     */
    public $col_name = 'name';
    
    /**
     * 数值标识
     */
    public $col_value = '';
    
    /**
     * 排序标识
     */
    public $col_sort = 'paixu';
    
    // 获取树型选项数组
    public static function treeOptions($parentId='0',$wheres=[],$level=0,$reload=false,$isKey=false)
    {
        $model = self::model();
        $cachekey = 'treeOptions/'.self::className().'/'.md5(serialize($parentId).serialize($wheres).serialize($isKey));
        $result = Yii::$app->cache->get($cachekey);
        if($reload===99) return Yii::$app->cache->delete($cachekey);
        if($result===false || $reload){
            $list = is_array($parentId)
                ? $parentId
                : self::find()->where($wheres)
                    ->andWhere([$model->col_parent=>$parentId])
                    ->orderBy($model->col_sort ? "{$model->col_sort} desc" : "")
                    ->with("childs.childs.childs.childs")
                    ->all();
            $result = [];
            foreach($list as $item){
                $childs = $wheres ? $item->getChilds()->andWhere($wheres)->all() : $item['childs'];
                $result[$item[$isKey ? $model->col_id : ($model->col_value ? $model->col_value : $model->col_id)]] = '|—'.str_repeat('—',$level*2).$item[$model->col_name];
                if($childs){
                    $result += self::treeOptions($childs,$wheres,($level+1),$reload,$isKey);
                }
            }
            Yii::$app->cache->set($cachekey,$result,86400);
        }
        return $result;
    }
    
    // 返回权限树型数据
    public static function treeData($parentId='0',$wheres=[],$selectIds=[],$reload=false)
    {
        $model = self::model();
        $cachekey = 'treeData/'.self::className().'/'.md5(serialize($parentId).serialize($wheres).serialize($selectIds));
        $result = Yii::$app->cache->get($cachekey);
        if($reload===99) return Yii::$app->cache->delete($cachekey);
        if($result===false || $reload){
            $list = is_array($parentId)
                ? $parentId
                : self::find()->where($wheres)
                    ->andWhere([$model->col_parent=>$parentId])
                    ->orderBy($model->col_sort ? "{$model->col_sort} desc" : "")
                    ->with("childs.childs.childs.childs")
                    ->all();
            $result = [];
            foreach($list as $item){
                $data = $item['attributes'];
                $data['id'] = $item[$model->col_id];
                $data['name'] = $item[$model->col_name];
                if($wheres){
                    $childs = $item->getChilds()->andWhere($wheres)->all();
                }else{
                    $childs = $item['childs'];
                }
                    
                if($childs) $data['children'] = self::treeData($childs,$wheres,$selectIds,$reload);
                
                $data['type'] = !empty($data['children']) ? 'folder' : 'item';
                if($selectIds && in_array($data[$model->col_id],$selectIds)) $data['selected'] = true;
                $result[] = $data;
            }
            Yii::$app->cache->set($cachekey,$result,86400);
        }
        
        return $result;
    }
    
    // 返回树型菜单数组
    public static function treeMenu($parentId='0',$wheres=[],$reload=false)
    {
        $model = self::model();
        $cachekey = 'treeMenu/'.self::className().'/'.md5(serialize($parentId).serialize($wheres));
        $result = Yii::$app->cache->get($cachekey);
        if($reload===99) return Yii::$app->cache->delete($cachekey);
        if($result===false || $reload){
            $list = is_array($parentId) 
                ? $parentId 
                : self::find()->where($wheres)
                    ->andWhere([$model->col_parent=>$parentId])
                    ->orderBy($model->col_sort ? "{$model->col_sort} desc" : "")
                    ->with("childs.childs.childs.childs")
                    ->all();
            $result = [];
            foreach($list as $item){
                $data = $item['attributes'];
                if($wheres){
                    $childs = $item->getChilds()->andWhere($wheres)->all();
                }else{
                    $childs = $item['childs'];
                }
                    
                if($childs) $data['childs'] = self::treeMenu($childs,$wheres,$reload);
                
                $result[] = $data;
            }
            Yii::$app->cache->set($cachekey,$result,86400);
        }
        
        return $result;
    }
    
    // 获取上级名称
    public function getV_parent()
    {
        return (isset($this->parent[$this->col_name]) ? $this->parent[$this->col_name] : '');
    }
    
    // 获取上级
    public function getParent()
    {
        return $this->hasOne(self::className(), [$this->col_id => $this->col_parent]);
    }
    
    // 获取子级
    public function getChilds()
    {
        if($this->col_sort){
            return $this->hasMany(self::className(), [$this->col_parent => $this->col_id])->orderBy("{$this->col_sort} desc");
        }
        return $this->hasMany(self::className(), [$this->col_parent => $this->col_id]);
    }
    
    // 获取顶级
    public function getTopParent()
    {
        if($this->parent){
            return (!empty($this->parent['parent']) ? $this->parent['parent'] : $this->parent);
        }
        return $this;
    }
    
    // 获取父级id
    public function getParentIds(){
        $ids = array($this[$this->col_id]);
        if($this->parent){
            $ids = array_merge($this->parent['parentIds'],$ids);
        }
        return $ids;
    }
    
    // 获取子级id
    public function getSubIds(){
        $ids = array($this[$this->col_id]);
        if($this->childs){
            foreach($this->childs as $item){
                $ids = array_merge($ids,$item['subIds']);
            }
        }
        return $ids;
    }
    
    // 刷新树型数据缓存
    public static function reloadCache($parentIds=null){
        self::treeOptions('0',[],0,99);
        self::treeData('0',[],[],99);
        self::treeMenu('0',[],99);

        $parentIds = is_array($parentIds) ? $parentIds : [$parentIds];
        foreach($parentIds as $parentId){
            $parentId = (string)$parentId;
            self::treeOptions($parentId,[],0,99);
            self::treeData($parentId,[],[],99);
            self::treeMenu($parentId,[],99);
            
            $parentId = (int)$parentId;
            self::treeOptions($parentId,[],0,99);
            self::treeData($parentId,[],[],99);
            self::treeMenu($parentId,[],99);
        }
    }
    
    public function save($runValidation = true, $attributeNames = null)
    {
        $parentIds = $this->parentIds;
        
        if(strlen($this[$this->col_parent])===0) $this[$this->col_parent] = '0';
        $result = parent::save($runValidation, $attributeNames);
        
        // 更新缓存
        $result && self::reloadCache($parentIds);
        
        return $result;
    }
    
    public function delete($clearCache=true)
    {
        $parentIds = $clearCache ? $this->parentIds : [];
        
        // 级联删除
        if($this->childs){
            foreach($this->childs as $item) $item->delete(false);
        }
        
        $result = parent::delete(false);

        // 更新缓存
        $clearCache && self::reloadCache($parentIds);
        
        return $result;
    }
}