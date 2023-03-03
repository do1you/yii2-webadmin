<?php
/**
 * 数据库表 "log_api_response" 的模型对象.
 * @property int $id 流水号
 * @property string $interface 接口名称
 * @property string $platform 终端系统
 * @property string $imei 终端标识
 * @property string $ip 终端IP
 * @property int $result_code 结果代码
 * @property string $result_msg 结果描述
 * @property string $params 参数
 * @property string $create_time 操作时间
 * @property int $user_id 操作用户
 */

namespace webadmin\modules\logs\models;

use Yii;

class LogApiResponse extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'log_api_response';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['interface', 'platform', 'imei', 'ip', 'result_code', 'result_msg', 'params', 'create_time','end_time'], 'safe'],
            [['user_id','run_millisec'], 'integer'],
            [['params'], 'string'],
            [['create_time'], 'safe'],
            [['interface'], 'string', 'max' => 80],
            [['platform', 'imei', 'result_msg'], 'string', 'max' => 255],
            [['ip'], 'string', 'max' => 16],
            [['result_code'], 'string', 'max' => 32],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('logs', '流水号'),
            'interface' => Yii::t('logs', '接口名称'),
            'platform' => Yii::t('logs', '终端系统'),
            'imei' => Yii::t('logs', '终端标识'),
            'ip' => Yii::t('logs', '终端IP'),
            'result_code' => Yii::t('logs', '结果代码'),
            'result_msg' => Yii::t('logs', '结果描述'),
            'params' => Yii::t('logs', '参数'),
            'create_time' => Yii::t('logs', '操作时间'),
            'end_time' => Yii::t('logs', '结束时间'),
            'run_millisec' => Yii::t('logs', '执行毫秒'),
            'user_id' => Yii::t('logs', '操作用户'),
        ];
    }
    
    // 搜索
    public function search($params,$wheres=[],$with=[], $joinWith=[])
    {
        $this->load($params);
        $wheres = [];
        
        // 操作时间
        if(strlen($this->create_time) && strpos($this->create_time, '至')){
            list($startTime, $endTime) = explode('至',$this->create_time);
            $wheres[] = ['>=','create_time',trim($startTime)];
            $wheres[] = ['<=','create_time',trim($endTime)];
            unset($this->create_time);
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
