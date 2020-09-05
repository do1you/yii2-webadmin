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
     * 增加乐观锁
     */
    public function optimisticLock()
    {
        return 'run_nums';
    }
    
    /**
     * 运行计划任务
     */
    public function run()
    {
        $app = Yii::$app;
        $this->run_state = 1;
        if(SysCrontab::cacheLock('SysCrontab/'.$this->command) && $this->save(false)){
            $result = SysCrontab::runCmd($this->command, [], true);
            
            $this->last_time = time();
            $this->run_state = $result===false ? 3 : 2;
            $this->save(false);
            
            SysCrontab::cacheLock('SysCrontab/'.$this->command, true);
        }
        Yii::$app = $app;
        
        return (isset($result) ? $result : false);
    }
    
    /**
     * 增加锁限制，调用系统的cache
     */
    public static function cacheLock($cacheKey=null, $isUnLock=false, $lockTime=60)
    {
        if(empty($cacheKey)) return false;
        if($isUnLock) Yii::$app->cache->delete($cacheKey);
        $time = time();
        $lastTime = Yii::$app->cache->get($cacheKey);
        if(empty($lastTime) || $time-$lastTime>=$lockTime){
            Yii::$app->cache->set($cacheKey,$time,$lockTime);
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 运行内置命令
     */
    public static function runCmd($command = '', $params = [], $isCmd = false)
    {
        set_time_limit(3600);
        ini_set('memory_limit', '-1');
        
        $params = is_array($params) ? $params : ($params ? json_decode($params) : []);
        if($isCmd){ // 命令行模式
            $processPath = Yii::getAlias('@vendor/../');
            $cmd = (strtoupper(substr(PHP_OS,0,3))=='WIN' ? true : false)
                ? $processPath.'yii.bat '.$command
                : $processPath.'yii '.$command;
            if($params) $cmd .= ' "' . implode('" "',$params).'"';
            exec($cmd,$result,$code);
            
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
}
