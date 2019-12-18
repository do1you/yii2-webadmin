<?php
/**
 * 数据库表 "auth_role" 的模型对象.
 * @property int $id ID
 * @property string $name 角色名称
 * @property int $is_system 是否系统预设
 * @property string $role_group 角色分组
 * @property string $note 备注
 */

namespace webadmin\modules\authority\models;

use Yii;
use yii\helpers\VarDumper;

class AuthRole extends \webadmin\ModelCAR
{
    public $authorityList;
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
            [['name', 'role_group'], 'required'],
            [['note'], 'safe'],
            [['authorityList'], 'safe', 'on'=>['insert', 'update']],
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
            'authorityList' => Yii::t('authority', '权限范围'),
        ];
    }
    
    // 获取是否系统预设
    public function getV_is_system($val = null){
        return \webadmin\modules\config\models\SysLdItem::dd('enum',($val!==null ? $val : $this->is_system));
    }
    
    // 获取角色分组
    public function getV_role_group($val = null){
        return \webadmin\modules\config\models\SysLdItem::dd('role_group_list',($val!==null ? $val : $this->role_group));
    }
    
    // 搜索
    public function search($params,$wheres=[],$with=[], $joinWith=[])
    {
        $this->load($params);

        // 是否系统预设角色
        if(strlen($this->is_system)){
            $ddVal = array_search($this->is_system, $this->getV_is_system(false));
            $this->is_system = strlen($ddVal) ? $ddVal : $this->is_system;
        }
        
        // 角色分组
        if(strlen($this->role_group)){
            $ddVal = array_search($this->role_group, $this->getV_role_group(false));
            $this->role_group = strlen($ddVal) ? $ddVal : $this->role_group;
        }
        
        return parent::search([],$wheres,$with,$joinWith);
    }
    
    public function afterSave($insert, $changedAttributes){
        // 保存分配权限
        if(in_array($this->scenario,['insert','update'])){
            $authIds = is_array($this->authorityList) ? $this->authorityList : [];
            foreach($this->authorithRels as $rel){
                if(!in_array($rel['authority_id'],$authIds)){
                    $rel->delete();
                }else{
                    unset($authIds[array_search($rel['authority_id'],$authIds)]);
                }
            }
            foreach($authIds as $rid){
                $rmodel = new AuthRoleAuthorith;
                $rmodel->role_id = $this->id;
                $rmodel->authority_id = $rid;
                $rmodel->save(false);
            }
        }
        return parent::afterSave($insert, $changedAttributes);
    }
    
    public function beforeDelete(){
        // 保存角色
        if($this->is_system){
            throw new \yii\web\HttpException(200,Yii::t('authority','内置的系统角色不允许删除'));
        }
        return parent::beforeDelete();
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
