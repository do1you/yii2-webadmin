<?php
/**
 * 数据库表 "sys_crontab" 的模型对象.
 * @property int $id 流水号
 * @property string $name 计划名称
 * @property string $command 执行脚本
 * @property string $crontab_type 周期类型0 间隔 1 定时
 * @property int $repeat_min 间隔时间(分钟)
 * @property int $timing_day 定时天数
 * @property string $timing_time 定时时间
 * @property int $run_nums 任务锁
 * @property string $last_time 最后执行时间
 * @property int $state 执行状态 0 正常 1 禁用
 * @property int $run_state 执行状态 0 未执行 1 执行中 2 执行成功 3 执行失败
 */

namespace webadmin\modules\config\models;

use Yii;

class SysCrontab extends \webadmin\ModelCAR
{    
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'sys_crontab';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['name', 'command'], 'required'],
            [['timing_day', 'repeat_min'], 'integer', 'min'=>1],
            [['last_time'], 'safe'],
            [['repeat_min', 'timing_day', 'run_nums', 'last_time', 'state', 'run_state'], 'integer'],
            [['timing_time'], 'safe'],
            [['name', 'command'], 'string', 'max' => 150],
            [['crontab_type'], 'string', 'max' => 20],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('config', '流水号'),
            'name' => Yii::t('config', '计划名称'),
            'command' => Yii::t('config', '执行脚本'),
            'crontab_type' => Yii::t('config', '周期类型'), // 0 间隔 1 定时
            'repeat_min' => Yii::t('config', '间隔时间'), // (分钟)
            'timing_day' => Yii::t('config', '定时天数'),
            'timing_time' => Yii::t('config', '定时时间'),
            'run_nums' => Yii::t('config', '任务锁'),
            'last_time' => Yii::t('config', '最后执行时间'),
            'state' => Yii::t('config', '状态'), // 0 正常 1 禁用
            'run_state' => Yii::t('config', '执行状态'), //  0 未执行 1 执行中 2 执行成功 3 执行失败
        ];
    }
    
    /**
     * 弹出任务
     * @return \webadmin\modules\config\models\SysCrontab
     */
    public static function reserve()
    {
        $time = time();
        $startSec = date('H:i:s');
        $payload = self::find()->where("
            state = 0 
            and ( run_state != '1' or (run_state = '1' and '{$time}'-last_time>=600) )
            and (
                (crontab_type = 0 and '{$time}'-last_time>=repeat_min*60)
                or 
                (
                    crontab_type = 1 
                    and '{$time}'-last_time>=timing_day*3600*24-3600*23
                    and time_to_sec(timing_time)<=time_to_sec('{$startSec}') 
                    and time_to_sec(timing_time)+1200>=time_to_sec('{$startSec}')
                )
            )
        ")
        ->orderBy(['last_time' => SORT_ASC])
        ->limit(1)
        ->one();
        
        unset($time,$startSec);
        if($payload){
            $payload->run_state = '1';
            $payload->last_time = time();
            try {
                if($payload->save(false)){
                    return $payload;
                }
            } catch (\yii\db\StaleObjectException $e) {
                // 捕获乐观锁，任务被其他进程执行了，弹出一条新的任务
                return self::reserve();
            }
        }
        return;
    }
    
    /**
     * 执行任务
     */
    public function handle()
    {
        // 增加锁
        if($this->lock()){
            try{
                $result = SysCrontab::runCmd($this->command, [], true);
            }catch(\Exception $e) { //捕获异常
                // 记录计划任务日志
                \webadmin\modules\logs\models\LogCrontab::insertion([
                    'command' => dirname($this->command),
                    'action' => basename($this->command),
                    'args' => '',
                    'exit_code' => '3',
                    'message' => $e->getMessage(),
                    'starttime' => date('Y-m-d H:i:s', $this->last_time),
                    'endtime' => date('Y-m-d H:i:s'),
                    'user_id' => '0',
                ]);
            }
            $this->unLock();
            return $result;
        }
        return false;
    }
    
    /**
     * 完成任务
     */
    public function release($result)
    {
        if($this->id){
            $this->last_time = time();
            $this->run_state = $result===false ? '3' : '2';
            $this->save(false);
        }
    }
    
    /**
     * 监听任务
     */
    public static function listen($maxNum = 9999, $timeout = 3)
    {
        $num = 0;
        while($maxNum === -1 || $num < $maxNum){
            if($maxNum !== -1) $num++;
            if ($payload = SysCrontab::reserve()) {
                //echo "\r\n{$num}:crontab-{$payload->id}";
                $payload->release($payload->handle());
                
                // 记录数据库操作日志
                \webadmin\modules\logs\models\LogDatabase::logmodel()->saveLog();
                
                unset($payload);
            } elseif ($timeout) {
                //echo "\r\n{$num}:sleep";
                sleep($timeout);
            }
        }
    }
    
    /**
     * 运行计划任务
     */
    public function run()
    {
        try {
            if($this->id){
                $this->run_state = '1';
                $this->last_time = time();
                if($this->save(false)){
                    $result = $this->handle();
                    $this->release($result);
                    return $result;
                }
            }
        } catch (\Exception $e) {
        }
        return false;
    }
    
    /**
     * 计划任务脚本加锁
     */
    public function lock()
    {
        return SysCrontab::cacheLock('SysCrontab/lock/'.$this->id);
    }
    
    /**
     * 计划任务脚本解锁
     */
    public function unLock()
    {
        return SysCrontab::cacheLock('SysCrontab/lock/'.$this->id, true);
    }
    
    /**
     * 增加锁限制，调用系统的cache
     */
    public static function cacheLock($cacheKey=null, $isUnLock=false, $lockTime=180)
    {
        if(empty($cacheKey)) return false;
        $cacheKey = "systemCacheLock/{$cacheKey}";
        
        // 解锁
        if($isUnLock) return Yii::$app->cache->delete($cacheKey); 
        
        // 加锁
        if(Yii::$app->cache->get($cacheKey)){
            return false;
        }else{
            Yii::$app->cache->set($cacheKey, date('Y-m-d H:i:s'), $lockTime);
            return true;
        }
    }
    
    /**
     * 运行内置命令
     */
    public static function runCmd($command = '', $params = [], $isCmd = false)
    {
        $params = is_array($params) ? $params : ($params ? json_decode($params) : []);
        if($isCmd){ // 命令行模式
            $isWindows = (strtoupper(substr(PHP_OS,0,3))=='WIN' ? true : false);
            $dir = Yii::getAlias('@runtime').DIRECTORY_SEPARATOR.'process'.DIRECTORY_SEPARATOR.'cmdlogs';
            \yii\helpers\FileHelper::createDirectory($dir);
            $path = $dir . DIRECTORY_SEPARATOR . date('Ymd').'.log';
            $putFile = $isWindows
                ? ' >> ' . $path . ' '// >nul
                : ' >> '. $path .' '; // 2>&1 &
            
            $processPath = Yii::getAlias('@vendor/../');
            $cmd = $isWindows
                ? $processPath.'yii.bat '.$command
                : $processPath.'yii '.$command;
            if($params) $cmd .= ' "' . implode('" "',$params).'"';
            $cmd .= $putFile;
            
            $startTime = date('Y-m-d H:i:s');
            exec($cmd,$result,$code);
            $isWindows 
            ? exec("echo \"{$startTime} TO ".date('Y-m-d H:i:s')." Command:({$command})\"".$putFile) 
            : exec("echo -e \"\\n{$startTime} TO ".date('Y-m-d H:i:s')." Command:({$command})\\n\"".$putFile);
            
            if(strlen($code)>0 && $code!='0') return false;
            if($result){
                return is_array($result) ? implode("\r\n", $result) : $result;
            }else{
                // 没有输出默认成功
                return true;
            }
        }else{
            $app = Yii::$app;
            if(($application = static::getConsoleApp())){
                ob_start();
                $result = $application->runAction($command, $params);
                $message = ob_get_contents();
                ob_end_clean();
                
                if($result=='0'){
                    $resp = ($message ? $message : true);
                }
                
                Yii::$app = $app;
            }
            return (isset($resp) ? $resp : false);
        }
    }
    
    /**
     * 返回命令行实例
     */
    public static $app;
    public static function getConsoleApp()
    {
        if(Yii::$app instanceof \yii\console\Application){
            return Yii::$app;
        }
        
        if(SysCrontab::$app){
            return SysCrontab::$app;
        }
        
        $config = !empty($GLOBALS['config']) ? $GLOBALS['config'] : null;
        if($config){
            // 重载配置
            unset(
                $config['components']['errorHandler'],
                $config['components']['urlManager'],
                $config['components']['user'],
                $config['components']['assetManager'],
                $config['components']['request'],
                $config['components']['session']
            );
            $config['controllerNamespace'] = 'console';
            
            SysCrontab::$app = new \yii\console\Application($config);
            return SysCrontab::$app;
        }
        
        return null;
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
        
        // 周期类型
        if(strlen($this->crontab_type)){
            $ddVal = array_search($this->crontab_type, $this->getV_crontab_type(false));
            $this->crontab_type = strlen($ddVal) ? $ddVal : $this->crontab_type;
        }
        
        // 执行状态
        if(strlen($this->run_state)){
            $ddVal = array_search($this->run_state, $this->getV_run_state(false));
            $this->run_state = strlen($ddVal) ? $ddVal : $this->run_state;
        }
        
        return parent::search([],$wheres,$with,$joinWith);
    }
    
    // 获取周期类型时间描述
    public function getV_crontab_type_str()
    {
        if($this->crontab_type=='1'){
            return Yii::t('config', '每').$this->timing_day.Yii::t('config', '天').$this->timing_time;
        }else{
            return Yii::t('config', '间隔').$this->repeat_min.Yii::t('config', '分钟');
        }
    }
    
    // 获取状态
    public function getV_state($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('record_status',($val!==null ? $val : $this->state));
    }
    
    // 获取状态
    public function getV_crontab_type($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('crontab_type',($val!==null ? $val : $this->crontab_type));
    }
    
    // 获取执行状态
    public function getV_run_state($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('crontab_run_state',($val!==null ? $val : $this->run_state));
    }
    
    /**
     * 增加乐观锁
     */
    public function optimisticLock()
    {
        return 'run_nums';
    }
}
