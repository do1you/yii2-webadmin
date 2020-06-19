<?php
/**
 * rest接口：获取通用树型数据
 */
namespace webadmin\restful;

use Yii;

class TreeAction extends \webadmin\restful\Action
{
    /**
     * 父级的标识名称
     */
    public $colParent = 'parent_id';
    
    /**
     * 顶级标识内容
     */
    public $valParent = '0';
    
    /**
     * 排序字段
     */
    public $colOrder;
    
    /**
     * 查询条件
     */
    public $colWhere;
    
    /**
     * 子级标识
     */
    public $colChilds;
    
    /**
     * 子级关联查询条件
     */
    public $colChildsWhere;
    
    /**
     * 标识级别
     */
    public $colChildLevel = 4;
    
    /**
     * 缓存时间
     */
    public $cacheTime = 7200;
    
    /**
     * 是否读取缓存数据
     */
    public $isReadCache = true;
    
    /**
     * 执行获取模型详情的树形业务数据
     * @return Tree Model
     */
    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }
        
        $id = Yii::$app->request->getBodyParam('id',Yii::$app->request->get('id'));
        $id = strlen($id)>0 ? $id : $this->valParent;
        
        if(is_callable($this->colWhere)){
            $this->colWhere = call_user_func($this->colWhere);
        }
        
        if(is_callable($this->colOrder)){
            $this->colOrder = call_user_func($this->colOrder);
        }
        
        if(is_callable($this->colChildsWhere)){
            $this->colChildsWhere = call_user_func($this->colChildsWhere);
        }
        
        return $this->_tree($id);
    }
    
    /**
     * 查询树型子列表数据
     */
    protected function _tree($id){
        $cacheKey = "treeData/{$this->modelClass}/".
                    ($this->colWhere ? md5(serialize($this->colWhere)).'/' : '').
                    ($this->colOrder ? md5(serialize($this->colOrder)).'/' : '').
                    ($this->colChilds ? md5(serialize($this->colChilds)).'/' : '').
                    "{$id}";       
        if($this->isReadCache){
            $result = Yii::$app->cache->get($cacheKey);
            if($result!==false) return $result;
        }
        
        $modelClass = $this->modelClass;
        $keys = $modelClass::primaryKey();
        $k = !empty($keys[0]) ? $keys[0] : 'id';
        
        $query = $modelClass::find()->andWhere([
            $this->colParent => $id,
        ]);
        
        // 关联查询
        if($this->colChilds){
            $query->with($this->colChildsWhere ? $this->colChildsWhere : trim(str_repeat($this->colChilds.'.',$this->colChildLevel),'.'));
        }
        
        // 条件查询
        if($this->colWhere){
            $query->andWhere($this->colWhere);
        }
        
        // 排序
        if($this->colOrder){
            $query->orderBy($this->colOrder);
        }
        
        $models = $query->all();
        $result = array();         
        foreach($models as $model){
            $item = $this->controller->serializeData($model);
            if(($childs = $this->colChilds ? $model[$this->colChilds] : $this->_tree($model[$k]))){
                $item['childs'] = $childs;
            }
            $result[] = $item;
            
        }
        
        Yii::$app->cache->set($cacheKey,$result,$this->cacheTime);
        
        return $result;
    }
}
