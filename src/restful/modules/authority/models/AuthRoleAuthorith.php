<?php
/**
 * 数据库表 "auth_role_authorith" 的模型对象.
 * @property int $id ID
 * @property int $role_id 角色
 * @property int $authority_id 权限
 */

namespace apiadmin\authority\models;

use Yii;

class AuthRoleAuthorith extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'auth_role_authorith';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['role_id', 'authority_id'], 'integer'],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('authority', 'ID'),
            'role_id' => Yii::t('authority', '角色'),
            'authority_id' => Yii::t('authority', '权限'),
        ];
    }
    
    // 获取角色
    public function getRole(){
        return $this->hasOne(AuthRole::className(), ['id' => 'role_id']);
    }
    
    // 获取权限
    public function getAuthority(){
        return $this->hasOne(AuthAuthority::className(), ['id' => 'authority_id']);
    }
}
