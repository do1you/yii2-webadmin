<?php
/**
 * 数据库表 "sys_ld_item" 的模型对象.
 * @property int $id ID
 * @property string $value 值
 * @property string $name 名称
 * @property string $ident 标识
 * @property int $parent_id 父节点
 * @property int $state 状态
 * @property int $reorder 排序
 * @property string $memo 描述
 */

namespace webadmin\modules\config\models;

use Yii;

class SysLdItem extends \webadmin\ModelCAR
{
    use \webadmin\TreeTrait;
    
    //是否记录数据库日志
    protected $isSaveLog = true;
    
    public function init()
    {
        $this->col_sort = 'reorder desc, name';
        $this->col_value = 'value';
        return parent::init();
    }
    
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'sys_ld_item';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['ident'], 'required', 'on'=>'ddparent'],
            [['value'], 'required', 'on'=>'ddchild'],
            [['value', 'name', 'ident', 'memo'], 'safe'],
            [['parent_id', 'state', 'reorder'], 'integer'],
            [['memo'], 'string'],
            [['value', 'name'], 'string', 'max' => 100],
            [['ident'], 'string', 'max' => 50],
        ];
    }
    
    /**
     * 获取数据字典
     */
    public static function dd($ident='',$value=false,$iskey=false,$reload=false)
    {
        $result = array();
        if($ident){
            $cachekey = 'config/ddlist'.($iskey ? '_key' : '').'/'.md5($ident);
            $result = Yii::$app->cache->get($cachekey);
            if($result===false || $reload){
                if(is_numeric($ident)){
                    $model = self::model()->findOne($ident);
                }else{
                    $model = self::model()->findOne(['ident'=>$ident]);
                }
                if(!empty($model)){
                    $result = self::treeOptions($model['id'],[],0,true);
                    foreach($result as $key=>$val){
                        if(strpos($val, '——')!==false){
                            $skipSplit = true;
                            break;
                        }
                    }
                    if(empty($skipSplit)){
                        foreach($result as $key=>$val){
                            $result[$key] = str_replace(array('|','—'),'',$val);
                        }
                    }
                }else{
                    $result = [];
                }
                
                Yii::$app->cache->set($cachekey,$result,86400);
            }
        }
        
        if($value!==false){
            return (isset($result[$value]) ? str_replace(array('|','—'),'',$result[$value]) : null);
        }else{
            return $result;
        }
    }
    
    public function afterSave($insert, $changedAttributes)
    {
        // 更新缓存
        if($this->parent_id && ($topParent = $this->topParent) && !empty($topParent['ident'])){
            self::dd($topParent['ident'],false,false,true);
        }
        
        return parent::afterSave($insert, $changedAttributes);
    }
    
    public function afterDelete()
    {
        // 更新缓存
        if($this->parent_id && ($topParent = $this->topParent) && !empty($topParent['ident'])){
            self::dd($topParent['ident'],false,false,true);
        }
        
        return parent::afterDelete();
    }
    
    // 搜索
    public function search($params,$wheres=[],$with=[], $joinWith=[])
    {
        $this->load($params);
        
        // 状态
        if(strlen($this->state)){
            $ddVal = array_search($this->state, $this->getV_state(false));
            $this->state = strlen($ddVal) ? $ddVal : $this->state;
        }
        
        return parent::search([],$wheres,$with,$joinWith);
    }
    
    // 获取状态
    public function getV_state($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('record_status',($val!==null ? $val : $this->state));
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('config', 'ID'),
            'value' => Yii::t('config', '选项'),
            'name' => $this->parent_id=='0' ? Yii::t('config', '字典名称') : Yii::t('config', '选项名称'),
            'ident' => Yii::t('config', '字典标识'),
            'parent_id' => Yii::t('config', '父节点'),
            'state' => Yii::t('config', '状态'),
            'reorder' => Yii::t('config', '排序'),
            'memo' => Yii::t('config', '描述'),
        ];
    }
}
