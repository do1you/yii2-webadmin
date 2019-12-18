<?php
/**
 * 数据库表 "log_imei" 的模型对象.
 * @property int $id 流水号
 * @property string $platform 终端系统
 * @property string $imei 终端标识
 * @property int $user_id 所属用户
 * @property string $create_time 创建时间
 */

namespace webadmin\modules\logs\models;

use Yii;

class LogImei extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'log_imei';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['platform', 'imei', 'create_time'], 'safe'],
            [['user_id'], 'integer'],
            [['create_time'], 'safe'],
            [['platform', 'imei'], 'string', 'max' => 255],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('logs', '流水号'),
            'platform' => Yii::t('logs', '终端系统'),
            'imei' => Yii::t('logs', '终端标识'),
            'user_id' => Yii::t('logs', '用户'),
            'create_time' => Yii::t('logs', '创建时间'),
        ];
    }
    
    // 更新iemi
    public static function upimei($data = [])
    {
        if(!empty($data['imei']) && !empty($data['user_id'])){
            $model = static::findOne([
                'user_id' => $data['user_id'],
            ]);
            if(!$model) $model = static::model();
            if($model->user_id!=$data['user_id'] && $model->load($data,'') && $model->save(false)){
                return $model->primaryKey;
            }
        }
        
        return false;
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
