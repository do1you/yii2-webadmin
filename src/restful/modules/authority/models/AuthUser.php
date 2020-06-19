<?php
/**
 * 数据表 "auth_user" 的模型对象.
 * @property int $id ID
 * @property string $name 姓名
 * @property string $login_name 用户名
 * @property string $password 密码
 * @property string $mobile 手机号
 * @property string $note 备注
 * @property int $state 状态
 * @property string $access_token 认证口令
 */
namespace apiadmin\authority\models;

use Yii;


class AuthUser extends \webadmin\ModelCAR implements \yii\web\IdentityInterface
{
    /**
     * 数据表名称
     */
    public static function tableName()
    {
        return 'auth_user';
    }

    /**
     * 属性规则
     */
    public function rules()
    {
        return [
            [['name', 'login_name', 'password', 'mobile', 'note'], 'required'],
            [['state'], 'integer'],
            [['name', 'login_name'], 'string', 'max' => 30],
            [['access_token'], 'string', 'max' => 32],
            [['mobile'], 'string', 'max' => 50],
            [['password'], 'string', 'max' => 60],
            [['note'], 'string', 'max' => 255],
        ];
    }

    /**
     * 属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('authority', 'ID'),
            'name' => Yii::t('authority', '姓名'),
            'login_name' => Yii::t('authority', '用户名'),
            'password' => Yii::t('authority', '密码'),
            'mobile' => Yii::t('authority', '手机号'),
            'note' => Yii::t('authority', '备注'),
            'state' => Yii::t('authority', '状态'),
            'access_token' => Yii::t('access_token', '认证口令'),
        ];
    }
    
    // 获取用户包含的角色关联关系
    public function getRoleRels(){
        return $this->hasMany(AuthUserRole::className(), ['user_id' => 'id']);
    }
    
    // 获取用户包含的角色
    public function getRoles(){
        return \yii\helpers\ArrayHelper::map($this->roleRels, 'role_id', 'role');
    }
    
    // 获取用户包含的权限
    private $_authoriths;
    public function getAuthoriths(){
        if($this->_authoriths === null){
            $roles = \yii\helpers\ArrayHelper::map($this->getRoleRels()->with('role.authorithRels.authority')->all(), 'role_id', 'role');
            $authoriths = [];
            foreach($roles as $role){
                foreach($role->authorithRels as $rel){
                    $authoriths[$rel['authority_id']] = $rel['authority'];
                }
            }
            $this->_authoriths = $authoriths;
        }
        
        return $this->_authoriths;
    }
    
    // 获取用户包含的权限IDS
    private $_authorithIds;
    public function getAuthorithIds(){
        if($this->_authorithIds === null){
            $roles = \yii\helpers\ArrayHelper::map($this->getRoleRels()->with('role.authorithRels')->all(), 'role_id', 'role');
            $authoriths = [];
            foreach($roles as $role){
                foreach($role->authorithRels as $rel){
                    $authoriths[$rel['authority_id']] = $rel['authority_id'];
                }
            }
            $this->_authorithIds = $authoriths;
        }
        
        return $this->_authorithIds;
    }
    
    /**
     * 返回数据时过滤字段
     */
    public function fields()
    {
        $array = parent::fields();
        unset($array['password']);
        return $array;
    }
    
    /**
     * 根据主键获取用户对象模型
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'state' => '0']);
    }
    
    /**
     * 根据口令获取用户对象模型
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token, 'state' => '0']);
    }
    
    /**
     * 返回用户主键标识
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }
    
    /**
     * 返回用户认证口令
     */
    public function getAuthKey()
    {
        return $this->access_token;
    }
    
    /**
     * 判断用户认证口令是否正确
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
    
    /**
     * 根据用户名获取用户对象模型
     */
    public static function findByUsername($username)
    {
        return static::findOne(['login_name' => $username, 'state' => '0']);
    }
    
    /**
     * 校验密码是否正确
     * @return boolean
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }
    
    /**
     * 密码进行加密
     * @param String $password
     */
    public function setPassword($password)
    {
        return ($this->password = Yii::$app->security->generatePasswordHash($password));
    }
}
