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
    public $password_confirm, $old_password, $password_curr, $roleList, $role_id;

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
            [['mobile'], 'match', 'pattern' => '/(^1[0-9]{10})$/', 'message' => '{attribute} 格式为11位手机号码'],
            [['login_name'], 'string', 'min' => 4, 'max' => 32],
            [['name'], 'string', 'min' => 2, 'max' => 32],
            [['state', 'sso_id'], 'integer'],
            [['access_token'], 'string', 'max' => 64],
            [['note', 'roleList', 'role_id', 'fail_num', 'unlock_time', 'reg_time', 'pass_time', 'last_time', 'password'], 'safe'],
            [['login_name', 'mobile'], 'unique', 'filter' => "state != -1"],

            // 修改资料
            [['name', 'mobile'], 'required', 'on' => 'info'],
            [['sso_id'], 'checksso', 'on' => 'info'],

            // 新增用户
            [['password'], 'required', 'on' => 'insert'],
            [['roleList'], 'required', 'on' => ['insert', 'update']],

            // 修改密码
            [['old_password', 'password', 'password_confirm'], 'required', 'on' => 'password'],
            [['password_confirm'], 'compare', 'compareAttribute' => 'password', 'message' => '两次输入的密码不一致', 'on' => 'password'],
            [['old_password'], 'validateOldPassword', 'on' => 'password'],
            [['password'], 'password_check', 'on' => 'password'],
        ];
    }
    //自定义密码验证
    public function password_check($attribute)
    {
        if (!$this->hasErrors()) {
            if (($result = $this->checkPassword($this->$attribute)) !== true) {
                $this->addError($attribute, $result);
            }
        }
    }
    /**
     * 校验SSO是否是被直接修改
     */
    public function checksso($attribute, $params)
    {
        if ($this->sso_id && $this->oldAttributes['sso_id'] && $this->oldAttributes['sso_id'] != $this->sso_id) {
            $this->addError('sso_id', '禁止修改用户中心ID！');
            return false;
        }

        return true;
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
            'sso_id' => Yii::t('authority', '用户中心ID'),
            'password_confirm' => Yii::t('authority', '确认新密码'),
            'old_password' => Yii::t('authority', '旧密码'),
            'password_curr' => Yii::t('authority', '当前密码'),
            'roleList' => Yii::t('authority', '角色'),
            'role_id' => Yii::t('authority', '角色'),
            'fail_num' => Yii::t('authority', '失败次数'),
            'unlock_time' => Yii::t('authority', '解锁时间'),
            'reg_time' => Yii::t('authority', '创建时间'),
            'pass_time' => Yii::t('authority', '密码到期时间'),
            'last_time' => Yii::t('authority', '最后登录时间'),
        ];
    }
    
    // 保存前动作
    public function beforeSave($insert)
    {
        if($insert){
            $this->reg_time = date('Y-m-d H:i:s');
        }
        
        return parent::beforeSave($insert);
    }

    public function afterSave($insert, $changedAttributes)
    {
        // 保存角色
        if (in_array($this->scenario, ['insert', 'update'])) {
            $roleIds = is_array($this->roleList) ? $this->roleList : [];
            foreach ($this->roleRels as $rel) {
                if (!in_array($rel['role_id'], $roleIds)) {
                    $rel->delete();
                } else {
                    unset($roleIds[array_search($rel['role_id'], $roleIds)]);
                }
            }
            foreach ($roleIds as $rid) {
                $rmodel = new AuthUserRole;
                $rmodel->user_id = $this->id;
                $rmodel->role_id = $rid;
                $rmodel->save(false);
            }
        }

        // 更新缓存
        static::model()->getCache('findOne', [['id' => $this->id, 'state' => '0']], 600, true);
        static::model()->getCache('findOne', [['access_token' => $this->access_token, 'state' => '0']], 600, true);

        return parent::afterSave($insert, $changedAttributes);
    }

    public function delete()
    {
        if ($this->id == '1') {
            throw new \yii\web\HttpException(200, Yii::t('authority', '内置管理员不允许删除.'));
        }
        return parent::delete();
    }
    
    public function behaviors()
    {
        return [
            'SecretKeyLockBehavior' => [
                'class' => \webadmin\behaviors\SecretKeyLockBehavior::className(),
                'lockAttribute' => 'secret_key',
                'secretAttribute' => ['login_name','password','mobile'],
            ],
        ];
    }
    
    // 获取状态中文
    public function getV_state($state = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('record_status', ($state !== null ? $state : $this->state));
    }

    // 获取角色名称
    public function getV_roleList()
    {
        $roles = \yii\helpers\ArrayHelper::map($this->roles, 'id', 'name');
        return implode(',', $roles);
    }

    // 搜索
    public function search($params, $wheres = [], $with = [], $joinWith = [])
    {
        $this->load($params);

        // 状态
        if (strlen($this->state)) {
            $ddVal = array_search($this->state, $this->getV_state(false));
            $this->state = strlen($ddVal) ? $ddVal : $this->state;
        }

        // 角色
        if (strlen($this->role_id)) {
            $joinWith[] = 'roleRels';
            $wheres[] = ['=', 'role_id', $this->role_id];
        }

        return parent::search([], $wheres, $with, $joinWith);
    }

    // 获取用户包含的角色关联关系
    public function getRoleRels()
    {
        return $this->hasMany(AuthUserRole::className(), ['user_id' => 'id']);
    }

    // 获取用户包含的角色
    public function getRoles()
    {
        return \yii\helpers\ArrayHelper::map($this->roleRels, 'role_id', 'role');
    }

    // 获取用户包含的权限
    private $_authoriths;
    public function getAuthoriths()
    {
        if ($this->_authoriths === null) {
            $roles = \yii\helpers\ArrayHelper::map($this->getRoleRels()->with('role.authorithRels.authority')->all(), 'role_id', 'role');
            $authoriths = [];
            foreach ($roles as $role) {
                foreach ($role->authorithRels as $rel) {
                    $authoriths[$rel['authority_id']] = $rel['authority'];
                }
            }
            $this->_authoriths = $authoriths;
        }

        return $this->_authoriths;
    }

    // 获取用户包含的权限IDS
    private $_authorithIds;
    public function getAuthorithIds()
    {
        if ($this->_authorithIds === null) {
            $roles = \yii\helpers\ArrayHelper::map($this->getRoleRels()->with('role.authorithRels')->all(), 'role_id', 'role');
            $authoriths = [];
            foreach ($roles as $role) {
                foreach ($role->authorithRels as $rel) {
                    $authoriths[$rel['authority_id']] = $rel['authority_id'];
                }
            }
            $this->_authorithIds = $authoriths;
        }

        return $this->_authorithIds;
    }

    // 获取用户包含的权限URL
    private $_authorithUrl;
    public function getAuthorithUrl()
    {
        if ($this->_authorithUrl === null) {
            $roles = \yii\helpers\ArrayHelper::map($this->getRoleRels()->with('role.authorithRels.authority')->all(), 'role_id', 'role');
            $authoriths = [];
            foreach ($roles as $role) {
                foreach ($role->authorithRels as $rel) {
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
        return static::model()->getCache('findOne', [['id' => $id, 'state' => '0']], 600);
    }

    /**
     * 根据口令获取用户对象模型
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return static::model()->getCache('findOne', [['access_token' => $token, 'state' => '0']], 600);
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
        $user = static::findOne(['login_name' => $username, 'state' => '0']);

        // 用户名不存在时根据手机号提取用户
        if (!$user) {
            $user = static::find()->where(['mobile' => $username, 'state' => '0'])->orderBy('id desc')->one();
        }

        return $user;
    }

    /**
     * 校验密码是否正确
     * @return boolean
     */
    public function validatePassword($password, $checkPass = false)
    {
        $checkPass = $checkPass !== false ? $checkPass : $this->password;
        if ($password && $checkPass) {
            if (strlen($checkPass) == 32) {
                return (strtolower(md5(md5($password))) == strtolower($checkPass));
            } else {
                return Yii::$app->security->validatePassword($password, $checkPass);
            }
        } else {
            return false;
        }
    }

    /**
     * 校验旧密码是否正确
     * @return boolean
     */
    public function validateOldPassword()
    {
        $result = $this->validatePassword($this->old_password, $this->password_curr);
        //$result = Yii::$app->security->validatePassword($this->old_password, $this->password_curr);
        $result || $this->addError('old_password', Yii::t('authority', '旧密码不正确'));
        return $result;
    }

    /**
     * 密码进行加密
     * @param String $password
     */
    public function setPassword($password)
    {
        if($this->scenario=='password'){ // 修改密码
            $this->pass_time = date('Y-m-d H:i:s', strtotime('+1 month'));
        }else{
            $this->pass_time = date('Y-m-d H:i:s'); // 后台创建密码
        }
        return ($this->password = Yii::$app->security->generatePasswordHash($password));
    }

    /**
     * 密码强度校验
     * @param String $password
     */
    public function checkPassword($password, $type = "all")
    {
        // 1）密码至少6位
        if (strlen($password) < 6) {
            return '密码至少6位';
        }
        // 2）应包含大写字母、小写字母、数字、特殊字符至少3种类型的组合
        if (!preg_match('/(?=.*[a-z])(?=.*[A-Z])(?=.*[\d\W])|(?=.*[a-z])(?=.*\d)(?=.*[\W])|(?=.*[A-Z])(?=.*\d)(?=.*[\W])/', $password)) {
            return '密码应包含大写字母、小写字母、数字、特殊字符至少3种类型的组合';
        }

        if ($type == 'all') {
            //默认验证全部
            // 3）新密码与旧密码不可完全相同
            if ($this->validatePassword($password, $this->password_curr)) {
                return '新密码与旧密码不可完全相同';
            }

            // 4）密码不可出现连续3个数字或字母，例如123456、11111、aaa、abc
            if (preg_match('/(.)(?=\1{2})/i', $password)) {
                return '密码不可出现连续的3个数字或字母，例如11111、aaa、222';
            }

            for ($i = 0; $i < strlen($password) - 2; $i++) {
                $char1 = $password[$i];
                $char2 = $password[$i + 1];
                $char3 = $password[$i + 2];

                if (is_numeric($char1) && is_numeric($char2) && is_numeric($char3)) {
                    if (ord($char1) + 1 == ord($char2) && ord($char2) + 1 == ord($char3)) {
                        return '密码不可出现连续顺序的3个数字或字母，例如123、456、789';
                    }
                }

                if (ctype_alpha($char1) && ctype_alpha($char2) && ctype_alpha($char3)) {
                    if (ord($char1) + 1 == ord($char2) && ord($char2) + 1 == ord($char3)) {
                        return '密码不可出现连续顺序的3个数字或字母，例如abc、efg、hij';
                    }
                }
            }

            // 5）密码不可包含用户名
            if (strpos($password, $this->login_name) !== false) {
                return '密码不可包含用户名';
            }
        }


        return true;
    }
}
