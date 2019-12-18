<?php
/**
 * 数据库表 "auth_user_role" 的模型对象.
 * @property int $id ID
 * @property int $user_id 管理用户
 * @property int $role_id 角色
 */

namespace webadmin\modules\authority\models;

use Yii;

class AuthUserRole extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'auth_user_role';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['user_id', 'role_id'], 'integer'],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('authority', 'ID'),
            'user_id' => Yii::t('authority', '管理用户'),
            'role_id' => Yii::t('authority', '角色'),
        ];
    }
    
    // 获取角色
    public function getRole(){
        return $this->hasOne(AuthRole::className(), ['id' => 'role_id']);
    }
    
    // 获取用户
    public function getUser(){
        return $this->hasOne(AuthUser::className(), ['id' => 'user_id']);
    }
}
