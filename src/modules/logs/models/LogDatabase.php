<?php
/**
 * 数据库表 "log_database" 的模型对象.
 * @property int $id ID
 * @property string $table_name 表名称
 * @property string $column_name 字段名称
 * @property string $primary_key 主键值
 * @property int $parent_id 父日志记录（操作日志分组用）
 * @property string $new_value 新值
 * @property string $old_value 旧值
 * @property string $operation 操作方式(操作所在功能的标识，自定义)
 * @property string $create_time 操作时间
 * @property int $user_id 操作用户
 */

namespace webadmin\modules\logs\models;

use Yii;

class LogDatabase extends \webadmin\ModelCAR
{
	/**
     * 记录数据库变更日志的实例
     */
    public static $logdb;

    /**
     * 需要保存的数据库日志记录
     */
    public $logs = [];
    
    /**
     * 不记录日志
     */
    protected $isSaveLog = false;
    
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'log_database';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['table_name', 'column_name', 'primary_key', 'parent_id', 'new_value', 'old_value', 'operation', 'create_time'], 'safe'],
            [['parent_id', 'user_id'], 'integer'],
            [['new_value', 'old_value'], 'string'],
            [['create_time'], 'safe'],
            [['table_name', 'column_name', 'primary_key'], 'string', 'max' => 50],
            [['operation'], 'string', 'max' => 30],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('logs', 'ID'),
            'table_name' => Yii::t('logs', '表名称'),
            'column_name' => Yii::t('logs', '字段名称'),
            'primary_key' => Yii::t('logs', '主键值'),
            'parent_id' => Yii::t('logs', '父日志记录'), // （操作日志分组用）
            'new_value' => Yii::t('logs', '新值'),
            'old_value' => Yii::t('logs', '旧值'),
            'operation' => Yii::t('logs', '操作场景'), // (操作所在功能的标识，自定义)
            'create_time' => Yii::t('logs', '操作时间'),
            'user_id' => Yii::t('logs', '操作用户'),
        ];
    }
    
    /**
     * 触发存储数据库日志动作
     */
    public function saveLog()
    {
        if($this->logs){
            $parentId = 0;
            $data = [];
            $colTitle = [];
            $time = date('Y-m-d H:i:s');
            foreach($this->logs as $item){
                list($table,$act,$oatts,$atts,$primaryKey) = $item;
                if(!empty($oatts) && is_array($oatts)){
                    foreach($oatts as $key=>$value){
                        $cols = [
                            'table_name' => $table,
                            'column_name' => $key,
                            'primary_key' => $primaryKey,
                            'parent_id' => $parentId,
                            'new_value' => (isset($atts[$key]) ? $atts[$key] : ''),
                            'old_value' => trim($value),
                            'operation' => trim($act),
                            'create_time' => $time,
                            'user_id' => ((Yii::$app instanceof \yii\web\Application && Yii::$app->user->id) ? Yii::$app->user->id : '0'),
                        ];
                        if(empty($parentId)){
                            $parentId = self::insertion($cols);
                        }else{
                            if(empty($colTitle)) $colTitle = array_keys($cols);
                            $data[] = array_values($cols);
                        }
                    }
                }
            }
            
            !empty($data) && self::getDb()
                ->createCommand()
                ->batchInsert(self::tableName(),$colTitle,$data)
                ->execute();
            $this->logs = [];
            unset($parentId,$data,$colTitle,$time,$item,$table,$act,$oatts,$atts,$primaryKey,$key,$value);
        }
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

	/**
	 * 返回静态资源类
	 * @return \webadmin\modules\logs\models\LogDatabase
	 */
    public static function logmodel()
	{
		if(!self::$logdb){
			self::$logdb = self::model();
		}
	    return self::$logdb;
	}
}
