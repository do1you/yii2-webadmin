<?php
/**
 * 数据库表 "log_user_action" 的模型对象.
 * @property int $id ID
 * @property string $remark 描述
 * @property string $action 动作
 * @property string $request 请求内容
 * @property string $addtime 时间
 * @property string $ip IP
 * @property int $user_id 操作用户
 */

namespace webadmin\modules\logs\models;

use Yii;

class LogUserAction extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'log_user_action';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['remark', 'action', 'request', 'addtime', 'ip'], 'safe'],
            [['request'], 'string'],
            [['addtime'], 'safe'],
            [['user_id'], 'integer'],
            [['remark', 'action'], 'string', 'max' => 60],
            [['ip'], 'string', 'max' => 32],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('logs', 'ID'),
            'remark' => Yii::t('logs', '描述'),
            'action' => Yii::t('logs', '动作'),
            'request' => Yii::t('logs', '请求内容'),
            'addtime' => Yii::t('logs', '时间'),
            'ip' => Yii::t('logs', 'IP'),
            'user_id' => Yii::t('logs', '操作用户'),
        ];
    }
    
    // 搜索
    public function search($params,$wheres=[],$with=[], $joinWith=[])
    {
        $this->load($params);
        $wheres = [];
        
        // 操作时间
        if(strlen($this->addtime) && strpos($this->addtime, '至')){
            list($startTime, $endTime) = explode('至',$this->addtime);
            $wheres[] = ['>=','addtime',trim($startTime)];
            $wheres[] = ['<=','addtime',trim($endTime)];
            unset($this->addtime);
        }
        
        // 用户
        if(strlen($this->user_id) && !is_numeric($this->user_id)){
            $joinWith[] = 'user';
            $wheres[] = ['like','name',trim($this->user_id)];
            unset($this->user_id);
        }
        
        $result = parent::search([],$wheres,$with,$joinWith);
        
        $this->load($params);
        
        return $result;
    }
    
    // 获取用户
    public function getUser(){
        return $this->hasOne(\webadmin\modules\authority\models\AuthUser::className(), ['id' => 'user_id']);
    }
}
