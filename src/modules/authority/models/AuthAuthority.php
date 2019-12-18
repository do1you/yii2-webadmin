<?php
/**
 * 数据库表 "auth_authority" 的模型对象.
 * @property int $id ID
 * @property string $name 名称
 * @property string $url 地址
 * @property int $parent_id 上级节点
 * @property int $flag 权限类型(0菜单 1动作)
 * @property int $can_allowed 是否总是允许
 * @property int $paixu 排序
 * @property string $icon 菜单图标
 */

namespace webadmin\modules\authority\models;

use Yii;

class AuthAuthority extends \webadmin\ModelCAR
{
    use \webadmin\TreeTrait;
    
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'auth_authority';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['name', 'url'], 'required'],
            [['parent_id', 'flag', 'can_allowed', 'paixu'], 'integer'],
            [['name', 'url'], 'string', 'max' => 80],
            [['icon'], 'string', 'max' => 50],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('authority', 'ID'),
            'name' => Yii::t('authority', '权限名称'),
            'url' => Yii::t('authority', '链接地址'),
            'parent_id' => Yii::t('authority', '上级节点'),
            'flag' => Yii::t('authority', '权限类型'), // (0菜单 1动作)
            'can_allowed' => Yii::t('authority', '是否总是允许'),
            'paixu' => Yii::t('authority', '排序'),
            'icon' => Yii::t('authority', '菜单图标'),
        ];
    }
    
    // 搜索
    public function search($params,$wheres=[],$with=[], $joinWith=[])
    {
        $this->load($params);
        
        // 权限属性
        if(strlen($this->flag)){
            $ddVal = array_search($this->flag, $this->getV_flag(false));
            $this->flag = strlen($ddVal) ? $ddVal : $this->flag;
        }
        
        // 是否总是允许
        if(strlen($this->can_allowed)){
            $ddVal = array_search($this->can_allowed, $this->getV_can_allowed(false));
            $this->can_allowed = strlen($ddVal) ? $ddVal : $this->can_allowed;
        }
        
        // 上级节点
        if(strlen($this->parent_id)){
            if(($models = self::findAll(['name'=>$this->parent_id]))){
                $parent_id = \yii\helpers\ArrayHelper::map($models,'id','id');
                $this->parent_id = count($parent_id)==1 ? current($parent_id) : $this->parent_id;
            }
        }

        return parent::search([],$wheres,$with,$joinWith);
    }
    
    // 返回所有权限控制内容
    public function authorityUrl(){
        $result = \yii\helpers\ArrayHelper::map(self::find()->all(),'url','url');
        
        return $result;
    }
    
    // 获取是否总是允许
    public function getV_can_allowed($val = null){
        return \webadmin\modules\config\models\SysLdItem::dd('enum',($val!==null ? $val : $this->can_allowed));
    }
    
    // 获取权限属性
    public function getV_flag($val = null){
        return \webadmin\modules\config\models\SysLdItem::dd('authority_flags',($val!==null ? $val : $this->flag));
    }
    
    // 获取角色关系
    public function getRoleRels(){
        return $this->hasMany(self::className(), ['authority_id' => 'id']);
    }
}
