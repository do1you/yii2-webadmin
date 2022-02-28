<?php
/**
 * 数据库表 "sys_config" 的模型对象.
 * @property string $key 主键
 * @property string $value 配置值
 * @property int $state 状态
 * @property string $group_id 分组
 * @property int $reorder 排序
 * @property string $label_name 名称
 * @property string $label_note 提示
 */

namespace webadmin\modules\config\models;

use Yii;

class SysConfig extends \webadmin\ModelCAR
{
    //是否记录数据库日志
    protected $isSaveLog = true;
    
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'sys_config';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['key', 'label_name'], 'required'],
            [['value', 'label_note', 'config_params'], 'safe'],
            [['value'], 'string'],
            [['state', 'reorder'], 'integer'],
            [['key'], 'string', 'max' => 100],
            [['group_id'], 'string', 'max' => 30],
            [['label_name', 'config_type'], 'string', 'max' => 50],
            [['label_note'], 'string', 'max' => 200],
            [['key'], 'unique'],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'key' => Yii::t('config', '主键'),
            'value' => Yii::t('config', '配置值'),
            'state' => Yii::t('config', '状态'),
            'group_id' => Yii::t('config', '分组'),
            'reorder' => Yii::t('config', '排序'),
            'label_name' => Yii::t('config', '标签名称'),
            'label_note' => Yii::t('config', '提示'),
            'config_type' => Yii::t('config', '配置类型'),
            'config_params' => Yii::t('config', '配置参数'),
        ];
    }
    
    // 根据键值获取配置
    public static function config($ident='',$defVal=null){
        $result = array();
        if($ident){
            $cachekey = 'config/configlist';
            $result = Yii::$app->cache->get($cachekey);
            if($result===false){
                $models = self::find()
                        ->andFilterWhere(['state'=>'0'])
                        ->orderBy('reorder desc')
                        ->all();
                $result = \yii\helpers\ArrayHelper::map($models,'key','v_value');
                Yii::$app->cache->set($cachekey,$result,86400);
            }
        }
        
        return (isset($result[$ident]) ? $result[$ident] : $defVal);
    }
    
    /**
     * 保存
     */
    public function beforeValidate()
    {
        if(is_array($this->value)){
            $this->value = implode(',',$this->value);
        }
        return parent::beforeValidate();
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
        
        // 分组
        if(strlen($this->group_id)){
            $ddVal = array_search($this->group_id, $this->getV_group_id(false));
            $this->group_id = strlen($ddVal) ? $ddVal : $this->group_id;
        }
        
        return parent::search([],$wheres,$with,$joinWith);
    }
    
    // 获取值
    public function getV_value()
    {
        return $this->value;    
    }
    
    // 获取状态
    public function getV_state($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('record_status',($val!==null ? $val : $this->state));
    }
    
    // 获取分组
    public function getV_group_id($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('config_group',($val!==null ? $val : $this->group_id));
    }
    
    // 获取分组对象
    public function getV_group_obj()
    {
        $list = [];
        $model = \webadmin\modules\config\models\SysLdItem::model()->findOne(['ident'=>'config_group']);
        if($model){
            $list = \webadmin\modules\config\models\SysLdItem::treeMenu($model['id'],['state'=>'0']);
        }
        return $list;
    }
    
    // 获取分组所有子对象
    public function getV_group_obj_child()
    {
        $result = [];
        $objList = $this->getV_group_obj();
        $childs = \yii\helpers\ArrayHelper::map($objList,'value','childs');
        if(isset($childs[$this->group_id])){
            $result = \yii\helpers\ArrayHelper::map($childs[$this->group_id],'value','value');
        }
        if($this->group_id){
            $result[$this->group_id] = $this->group_id;
        }
        return ($result ? $result : null);
    }
    
    // 获取配置类型
    public function getV_config_type($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('config_type',($val!==null ? $val : $this->config_type));
    }
    
    // 配置参数数组
    public function getV_config_params()
    {
        $config_params = $this->config_params ? $this->config_params : "";
        $result = [];
        $list = explode("\n", $config_params);
        if($list){
            foreach($list as $val){
                if(stripos($val,'|')===false){
                    $result[$val] = $val;
                }else{
                    list($k,$v) = explode('|',$val);
                    $result[$k] = $v;
                }
            }
        }
        return $result;
    }
    
    // 配置参数网络
    public function getV_config_ajax()
    {
        $config_params = $this->config_params ? $this->config_params : "";
        if(stripos($config_params, '.')!==false){
            return \yii\helpers\Url::to(['/config/sys-config/select2','key'=>$this->key]);
        }else{
            return \yii\helpers\Url::to($config_params);
        }
    }
}
