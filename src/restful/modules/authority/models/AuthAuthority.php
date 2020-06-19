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

namespace apiadmin\authority\models;

use Yii;

class AuthAuthority extends \webadmin\ModelCAR
{
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
            'name' => Yii::t('authority', '名称'),
            'url' => Yii::t('authority', '地址'),
            'parent_id' => Yii::t('authority', '上级节点'),
            'flag' => Yii::t('authority', '权限类型'), // (0菜单 1动作)
            'can_allowed' => Yii::t('authority', '是否总是允许'),
            'paixu' => Yii::t('authority', '排序'),
            'icon' => Yii::t('authority', '菜单图标'),
        ];
    }
    
    // 获取上级
    public function getParent(){
        return $this->hasOne(AuthAuthority::className(), ['id' => 'parent_id']);
    }
    
    // 获取子级
    public function getChilds(){
        return $this->hasMany(AuthAuthority::className(), ['parent_id' => 'id'])->orderBy('paixu desc');
    }
    
    // 获取角色关系
    public function getRoleRels(){
        return $this->hasMany(AuthRoleAuthorith::className(), ['authority_id' => 'id']);
    }
}
