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
namespace webadmin\modules\authority\models;

use Yii;


class AuthUser extends \webadmin\ModelCAR implements \yii\web\IdentityInterface
{
    public $password_confirm,$old_password,$password_curr,$roleList,$role_id;
    
    //是否记录数据库日志
    protected $isSaveLog = true;

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
            // 通用规则
            [['name', 'login_name', 'state'], 'required'],
            [['mobile'], 'match', 'pattern' => '/(^1[0-9]{10})$/','message' => '{attribute} 格式为11位手机号码'],
            [['password'], 'string', 'min'=>6, 'max' => 64],
            [['login_name'], 'string', 'min'=>4, 'max' => 32],
            [['name'], 'string', 'min'=>2, 'max' => 32],
            [['state'], 'integer'],
            [['access_token'], 'string', 'max' => 64],
            [['note', 'roleList', 'role_id'], 'safe'],
            [['login_name', 'mobile'], 'unique', 'filter'=>"state != -1"],
            
            // 修改资料
            [['name', 'mobile'], 'required', 'on'=>'info'],
            
            // 新增用户
            [['password'], 'required', 'on'=>'insert'],
            [['roleList'], 'required', 'on'=>['insert','update']],
            
            // 修改密码
            [['old_password', 'password', 'password_confirm'], 'required', 'on'=>'password'],
            [['password_confirm'], 'compare', 'compareAttribute' => 'password', 'message'=>'两次输入的密码不一致', 'on'=>'password'],
            [['old_password'], 'validateOldPassword', 'on'=>'password'],
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
            'access_token' => Yii::t('authority', '认证口令'),
            'password_confirm' => Yii::t('authority', '确认新密码'),
            'old_password' => Yii::t('authority', '旧密码'),
            'password_curr' => Yii::t('authority', '当前密码'),
            'roleList' => Yii::t('authority', '角色'),
            'role_id' => Yii::t('authority', '角色'),
        ];
    }
    
    public function afterSave($insert, $changedAttributes)
    {
        // 保存角色
        if(in_array($this->scenario,['insert','update'])){
            $roleIds = is_array($this->roleList) ? $this->roleList : [];
            foreach($this->roleRels as $rel){
                if(!in_array($rel['role_id'],$roleIds)){
                    $rel->delete();
                }else{
                    unset($roleIds[array_search($rel['role_id'],$roleIds)]);
                }
            }
            foreach($roleIds as $rid){
                $rmodel = new AuthUserRole;
                $rmodel->user_id = $this->id;
                $rmodel->role_id = $rid;
                $rmodel->save(false);
            }
        }
        return parent::afterSave($insert, $changedAttributes);
    }
    
    public function delete()
    {
        if($this->id=='1'){
            throw new \yii\web\HttpException(200,Yii::t('authority','内置管理员不允许删除.'));
        }
        return parent::delete();
    }
    
    // 获取状态中文
    public function getV_state($state = null){
        return \webadmin\modules\config\models\SysLdItem::dd('record_status',($state!==null ? $state : $this->state));
    }
    
    // 获取角色名称
    public function getV_roleList(){
        $roles = \yii\helpers\ArrayHelper::map($this->roles, 'id', 'name');
        return implode(',',$roles);
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
        
        // 角色
        if(strlen($this->role_id)){
            $joinWith[] = 'roleRels';
            $wheres[] = ['=','role_id',$this->role_id];
        }
        
        return parent::search([],$wheres,$with,$joinWith);
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
    
    // 获取用户包含的权限URL
    private $_authorithUrl;
    public function getAuthorithUrl(){
        if($this->_authorithUrl === null){
            $roles = \yii\helpers\ArrayHelper::map($this->getRoleRels()->with('role.authorithRels.authority')->all(), 'role_id', 'role');
            $authoriths = [];
            foreach($roles as $role){
                foreach($role->authorithRels as $rel){
                    $authoriths[$rel['authority_id']] = $rel['authority']['url'];
                }
            }
            $this->_authorithUrl = $authoriths;
        }
        
        return $this->_authorithUrl;
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
     * 校验旧密码是否正确
     * @return boolean
     */
    public function validateOldPassword()
    {
        $result = Yii::$app->security->validatePassword($this->old_password, $this->password_curr);
        $result || $this->addError('old_password', Yii::t('authority', '旧密码不正确'));
        return $result;
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
