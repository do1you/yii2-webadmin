<?php
/**
 * 数据库表 "log_crontab" 的模型对象.
 * @property int $id ID
 * @property string $command 脚本
 * @property string $action 动作
 * @property string $args 参数
 * @property int $exit_code 结果
 * @property string $message 描述
 * @property string $starttime 开始时间
 * @property string $endtime 结束时间
 * @property int $user_id 触发用户
 */

namespace webadmin\modules\logs\models;

use Yii;

class LogCrontab extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'log_crontab';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['command', 'action', 'args', 'message', 'starttime', 'endtime'], 'safe'],
            [['exit_code', 'user_id'], 'integer'],
            [['starttime', 'endtime'], 'safe'],
            [['command', 'action'], 'string', 'max' => 50],
            [['args'], 'string', 'max' => 255],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('logs', 'ID'),
            'command' => Yii::t('logs', '脚本'),
            'action' => Yii::t('logs', '动作'),
            'args' => Yii::t('logs', '参数'),
            'exit_code' => Yii::t('logs', '结果'),
            'message' => Yii::t('logs', '描述'),
            'starttime' => Yii::t('logs', '开始时间'),
            'endtime' => Yii::t('logs', '结束时间'),
            'user_id' => Yii::t('logs', '触发用户'),
        ];
    }
    
    // 搜索
    public function search($params,$wheres=[],$with=[], $joinWith=[])
    {
        $this->load($params);
        $wheres = [];
        
        // 操作时间
        if(strlen($this->starttime) && strpos($this->starttime, '至')){
            list($startTime, $endTime) = explode('至',$this->starttime);
            $wheres[] = ['>=','starttime',trim($startTime)];
            $wheres[] = ['<=','starttime',trim($endTime)];
            unset($this->starttime);
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
