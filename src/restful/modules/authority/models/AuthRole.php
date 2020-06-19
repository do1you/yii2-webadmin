<?php
/**
 * 数据库表 "auth_role" 的模型对象.
 * @property int $id ID
 * @property string $name 角色名称
 * @property int $is_system 是否系统预设
 * @property string $role_group 角色分组
 * @property string $note 备注
 */

namespace apiadmin\authority\models;

use Yii;

class AuthRole extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'auth_role';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['name', 'role_group', 'note'], 'required'],
            [['is_system'], 'integer'],
            [['name'], 'string', 'max' => 50],
            [['role_group'], 'string', 'max' => 20],
            [['note'], 'string', 'max' => 255],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('authority', 'ID'),
            'name' => Yii::t('authority', '角色名称'),
            'is_system' => Yii::t('authority', '是否系统预设'),
            'role_group' => Yii::t('authority', '角色分组'),
            'note' => Yii::t('authority', '备注'),
        ];
    }
    
    // 获取权限关系
    public function getAuthorithRels(){
        return $this->hasMany(AuthRoleAuthorith::className(), ['role_id' => 'id']);
    }
    
    // 获取用户关系
    public function getUserRels(){
        return $this->hasMany(AuthUserRole::className(), ['role_id' => 'id']);
    }
    
    // 获取包含的权限
    public function getAuthoriths(){
        return \yii\helpers\ArrayHelper::map($this->getAuthorithRels()->with('authority')->all(), 'authority_id', 'authority');
    }
    
    // 获取包含的用户
    public function getUsers(){
        return \yii\helpers\ArrayHelper::map($this->getUserRels()->with('user')->all(), 'user_id', 'user');
    }
}
