<?php
/**
 * 数据库表 "sys_queue" 的模型对象.
 * @property int $id ID
 * @property string $taskphp 任务处理脚本
 * @property string $params 参数
 * @property int $state 状态
 * @property int $user_id 用户
 * @property string $create_time 创建时间
 * @property string $start_time 开始时间
 * @property string $done_time 完成时间
 * @property int $callback 回调
 * @property int $run_nums 队列锁
 */

namespace webadmin\modules\config\models;

use Yii;

class SysQueue extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'sys_queue';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['taskphp', 'params', 'create_time', 'start_time', 'done_time'], 'required'],
            [['params'], 'string'],
            [['state', 'user_id', 'callback', 'run_nums'], 'integer'],
            [['create_time', 'start_time', 'done_time'], 'safe'],
            [['taskphp'], 'string', 'max' => 255],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('config', 'ID'),
            'taskphp' => Yii::t('config', '任务处理脚本'),
            'params' => Yii::t('config', '参数'),
            'state' => Yii::t('config', '状态'),
            'user_id' => Yii::t('config', '用户'),
            'create_time' => Yii::t('config', '创建时间'),
            'start_time' => Yii::t('config', '开始时间'),
            'done_time' => Yii::t('config', '完成时间'),
            'callback' => Yii::t('config', '回调'),
            'run_nums' => Yii::t('config', '队列锁'),
        ];
    }
    
    
    
    /**
     * 加入队列
     */
    public static function addQueue($data=[]){
        if($data){
            $data = is_string($data) ? array('taskphp'=>$data) : $data;
            return self::queue($data['taskphp'],(isset($data['params']) ? $data['params'] : []));
        }
        return false;
    }
    
    /**
     * 插入队列，执行方法，参数，其他参数
     */
    public static function queue($route = '', $params = [], $data = [])
    {
        if($route){
            $data['taskphp'] = $route;
            $data['params'] = json_encode($params);
            $isWebMode = Yii::$app instanceof \yii\web\Application;
            if(!isset($data['user_id'])) $data['user_id'] = $isWebMode&&Yii::$app->user->id ? Yii::$app->user->id : '0';
            $data['create_time'] = date('Y-m-d H:i:s');
            return \webadmin\modules\config\models\SysQueue::insertion($data);
        }
        return false;
    }
    
    /**
     * 运行队列
     */
    public function run()
    {
        if($this->lock()){
            try{
                $this->state = 1;
                $this->start_time = date('Y-m-d H:i:s');
                if($this->save(false)){
                    $result = \webadmin\modules\config\models\SysCrontab::runCmd($this->taskphp, $this->params, true);
                    
                    $this->done_time = date('Y-m-d H:i:s');
                    $this->state = $result===false ? 3 : 2;
                    if($this->state=='2' && !$this->callback){ // 完成状态且不用回调的直接删除
                        $this->delete();
                    }else{
                        $this->save(false);
                    }
                }
            }catch(\Exception $e) { //捕获异常
                // 记录计划任务日志
                \webadmin\modules\logs\models\LogCrontab::insertion([
                    'command' => dirname($this->taskphp),
                    'action' => basename($this->taskphp),
                    'args' => trim(trim(json_encode($this->params),'['),']'),
                    'exit_code' => '3',
                    'message' => $e->getMessage(),
                    'starttime' => $this->start_time,
                    'endtime' => date('Y-m-d H:i:s'),
                    'user_id' => '0',
                ]);
            }
            $this->unLock();
        }
        
        return (isset($result) ? $result : false);
    }
    
    /**
     * 队列脚本加锁
     */
    public function lock()
    {
        return SysCrontab::cacheLock('SysQueue/'.$this->id);
    }
    
    /**
     * 队列脚本解锁
     */
    public function unLock()
    {
        return SysCrontab::cacheLock('SysQueue/'.$this->id, true);
    }
    
    /**
     * 增加乐观锁
     */
    public function optimisticLock()
    {
        return 'run_nums';
    }
}
