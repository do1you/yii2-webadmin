<?php
namespace webadmin\modules\daemon\console;

use Yii;
use \yii\helpers\Console;
use \webadmin\modules\config\models\SysCrontab;
use \webadmin\modules\config\models\SysQueue;
use webadmin\modules\config\models\SysConfig;

class DefaultController extends \webadmin\console\CController
{
    /**
     * 缓存任务执行信息
     */
    public $cacheRuns = [];
    
    /**
     * 进程最多可执行次数
     */
    public $maxProcessNum = 1200;
    
    /**
     * 是否windows系统
     */
    public $isWindows = false;
    
    /**
     * 后台进程
     */
    public $processCmd = 'daemon/default/daemon';
    
    /**
     * 进程名称
     */
    public $processName = 'yalalat payment';
    
    /**
     * 命令路径
     */
    public $processPath;
    
    /**
     * 进程ID文件路径
     */
    public $pidFile;
    
    /**
     * 初始化
     */
    public function init()
    {
        $this->isWindows = strtoupper(substr(PHP_OS,0,3))=='WIN' ? true : false;
        $this->pidFile = Yii::getAlias('@runtime').DIRECTORY_SEPARATOR.'process'.DIRECTORY_SEPARATOR.'tongyihenshuai.pid';
        $this->processPath = Yii::getAlias('@app/../');
        \yii\helpers\FileHelper::createDirectory(dirname($this->pidFile));
    }
    
    /**
     * 不记录日志
     */
    public function afterAction($action, $result)
    {
    }
    
    /**
     * 守护进程
     */
    public function actionIndex($act=null)
    {
        switch($act)
        {
            case 'rstart': // 计划任务启动
                $this->actionRstart();
			case 'restart': // 重启
                $this->actionRestart();
                break;
            case 'start': // 启动
                $this->actionStart();
                break;
            case 'stop': // 停止
                $this->actionStop();
                break;
            case 'status': // 运行状态
                $this->actionStatus();
                break;
            case 'help': // 帮助
                $this->actionHelp();
                break;
            default: // 未知错误
                $act = $act ? $act : 'empty';
                Console::output("Unknown parameter {$act}.");
                break;
        }
        return 0;
    }
    
    /**
     * 守护进程
     * yii daemon/default/daemon
     */
    public function actionDaemon()
    {
        set_time_limit(0);
        if(($pid = $this->_processes())){
            $state = $this->ansiFormat('already started', Console::FG_RED);
            Console::output("processes {$this->processName} {$state}({$pid}).");
        }else{
            $pid = getmypid();
            file_put_contents($this->pidFile, $pid);
        }
        
        while(true){
            // 处理队列进程数
            $queueNums = SysConfig::config('config_queue_processes',3) - count($this->_processesPid(true,'run-queue'));
            if($queueNums > 0){
                for($i=0;$i<$queueNums;$i++){
                    $this->_run('daemon/default/run-queue');
                }
            }
            
            // 处理计划任务进程数
            $queueNums = SysConfig::config('config_crontab_processes',1) - count($this->_processesPid(true,'run-crontab'));
            if($queueNums > 0){
                for($i=0;$i<$queueNums;$i++){
                    $this->_run('daemon/default/run-crontab');
                }
            }
            
            sleep(3);
        }
        return 0;
    }
    
    /**
     * 启动，考虑兼容性，不用fork模式，采用exec进程来实现
     * LINUX：nohup yii daemon/default/daemon 2>&1 &
     * WINDOWS：start /b "" "%cd%\yii.bat daemon/default/daemon "
     * yii daemon/default/start
     */
    public function actionStart()
    {
        $pid = $this->_processes();
        if(empty($pid)){
            $this->_run($this->processCmd);
            usleep(250000);
            if(($pid = $this->_processes())){
                $state = $this->ansiFormat('startup success', Console::FG_GREEN);
                Console::output("processes {$this->processName} {$state}({$pid}).");
            }else{
                $state = $this->ansiFormat('startup fail', Console::FG_RED);
                Console::output("processes {$this->processName} {$state}.");
            }
        }else{
            $state = $this->ansiFormat('already started', Console::FG_RED);
            Console::output("processes {$this->processName} {$state}({$pid}).");
        }
        
        return 0;
    }
    
    /**
     * 停止
     * ps -ef |grep daemon/default/daemon |awk '{print $2}'|xargs kill -9
     * taskkill /f /fi "windowtitle eq *daemon*"
     * for /f "tokens=2" %a in ('tasklist /v ^| findstr /i "daemon/default/daemon"') do taskkill /f /t /pid %a
     * yii daemon/default/stop
     */
    public function actionStop()
    {
        // 关闭主进程
        $pid = $this->_processes();
        if(empty($pid)){
            $state = $this->ansiFormat('not started', Console::FG_RED);
            Console::output("processes {$this->processName} {$state}.");
        }else{
            $this->_kill($pid);
            unlink($this->pidFile);
            $state = $this->ansiFormat('Shutting down success', Console::FG_GREEN);
            Console::output("processes {$this->processName} {$state}.");
        }
        
        // 关闭队列进程
        $pids = $this->_processesPid(true,'run-queue');
        if($pids){
            foreach($pids as $pid){
                $this->_kill($pid);
                $this->_processesPid($pid,'run-queue');
            }
        }
        
        // 关闭计划任务进程
        $pids = $this->_processesPid(true,'run-crontab');
        if($pids){
            foreach($pids as $pid){
                $this->_kill($pid);
                $this->_processesPid($pid,'run-crontab');
            }
        }
        
        return 0;
    }
    
    /**
     * 状态
     * yii daemon/default/status
     */
    public function actionStatus()
    {
        $pid = $this->_processes();
        if(empty($pid)){
            $state = $this->ansiFormat('not started', Console::FG_RED);
            Console::output("processes {$this->processName} {$state}.");
        }else{
            $state = $this->ansiFormat('is started', Console::FG_GREEN);
            Console::output("processes {$this->processName} {$state}({$pid}).");
        }
        return 0;
    }
    
    /**
     * 重启
     * yii daemon/default/restart
     */
    public function actionRestart()
    {
        $this->actionStop();
        $this->actionStart();
        return 0;
    }

	/**
     * 判断进程是否在存，不存在则启动
     * yii daemon/default/rstart
	 * if [ "$(ps aux | grep yii | grep daemon/default/daemon)" == '' ]; then /home/wwwroot/pay.yalalat.com/yii daemon restart;  fi
     */
    public function actionRstart()
    {
		$cmd = 'ps aux | grep yii | grep '.$this->processCmd;
		exec($cmd,$result,$code);
		if(empty($result)){
			$this->actionRestart();
		}
        return 0;
    }
    
    /**
     * 帮助
     */
    public function actionHelp()
    {
        Console::output("command list: start | stop | restart | status | help.");
    }
    
    /**
     * 执行命令
     */
    private function _run($cmd = '')
    {
        $dir = dirname($this->pidFile).DIRECTORY_SEPARATOR.'logs';
        \yii\helpers\FileHelper::createDirectory($dir);
        $path = $dir . DIRECTORY_SEPARATOR . date('Ym').'.log';
        $cmd = $this->isWindows
        ? 'start /b '.$this->processPath.'yii.bat '.$cmd.' >> ' . $path . ' >nul'// >nul
        : 'nohup '.$this->processPath.'yii '.$cmd.' >> '. $path .' 2>&1 &'; // 2>&1
        @pclose(popen($cmd, 'r'));
    }
    
    /**
     * 删除进程
     */
    private function _kill($pid=null)
    {
        if(is_array($pid)){
            foreach($pid as $p){
                $this->_kill($p);
            }
        }elseif($pid){
            $cmd = $this->isWindows
            ? "taskkill /f /t /pid {$pid} >nul"
            : "kill -9 {$pid}";
            @pclose(popen($cmd, 'r'));
        }
    }
    
    /**
     * 查询进程
     */
    private function _find($pid=null)
    {
        if($pid){
            if($this->isWindows){
                $cmd = 'tasklist /fi "pid eq '.$pid.'"';
                exec($cmd,$result,$code);
                foreach($result as $line=>$str){
                    if(strstr($str,$pid)!==false){
                        return true;
                    }
                }
            }elseif(file_exists('/proc/'.$pid)){
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 获取进程ID
     */
    private function _processes()
    {
        $pid = file_exists($this->pidFile) ? file_get_contents($this->pidFile) : 0;
        return $pid;
    }
    
    /**
     * 执行计划任务
     * yii daemon/default/run-crontab
     */
    public function actionRunCrontab()
    {
        set_time_limit(0);
        $pid = $this->_processesPid();
        while($this->_increase()<=$this->maxProcessNum){
            $time = time();
            $startSec = date('H:i:s');
            $endSec = date('H:i:s',$time+3600);
            
            $tasks = SysCrontab::find()->where("
                state = 0 and (
                    run_state != 1 or (run_state = 1 and '{$time}'-last_time>=60)
                )
                and (
                    (crontab_type = 0 and '{$time}'-last_time>=repeat_min*60)
                    or (crontab_type = 1 and '{$time}'-last_time>=timing_day*3600*24-3600*23
                        and time_to_sec(timing_time)<=time_to_sec('{$startSec}') and time_to_sec(timing_time)+1200>=time_to_sec('{$startSec}')
                       )
                )
                    order by id asc limit 1
                ")->all();
            
            if($tasks){
                foreach($tasks as $task){
                    $result = $task->run();
                    if(is_string($result)) echo $result."\n";
                }
            }
            
            unset($time,$startSec,$endSec,$tasks,$result,$transaction);
            sleep(1);
        }
        $this->_processesPid($pid);
        
        return 0;
    }
    
    /**
     * 执行队列
     * yii daemon/default/run-queue
     */
    public function actionRunQueue()
    {
        set_time_limit(0);
        $pid = $this->_processesPid();
        while(($num = $this->_increase())<=$this->maxProcessNum){
            $time = time();
            
            $tasks = SysQueue::find()->where("state=0 order by id asc limit 1")->all();
            
            if($tasks){
                foreach($tasks as $task){
                    $result = $task->run();
                    if(is_string($result)) echo $result."\n";
                }
            }
            
            // 清理失败和卡住的队列
            if($num==1 || $num==$this->maxProcessNum || $num%100==0){
                $thDay = date('Y-m-d H:i:s',time()-86400*3); // 失败的删除三天前数据
                $mDay = date('Y-m-d H:i:s',time()-3600*2); // 执行中的删除两个小时前的数据
                SysQueue::deleteAll("(state=3 and start_time<='{$thDay}') or (state=1 and start_time<='{$mDay}')");
            }
            
            sleep(1);
        }
        $this->_processesPid($pid);
        
        return 0;
    }
    
    /**
     * 记录当前脚本的进程ID
     */
    private function _processesPid($pid=false,$act=null)
    {
        $act = $act ? $act : $this->action->id;
        $dir = dirname($this->pidFile).DIRECTORY_SEPARATOR.$act;
        if($pid===true){ // 获取所有pid
            $pid = [];
            if(file_exists($dir)){
                $files = \yii\helpers\FileHelper::findFiles($dir);
                foreach($files as $file){
                    $fid = file_get_contents($file);
                    
                    // 判断进程是否存在
                    if($this->_find($fid)){
                        $pid[] = $fid;
                    }else{
                        unlink($file);
                    }
                }
            }
        }elseif($pid){ // 删除pid
            if(file_exists($dir.DIRECTORY_SEPARATOR.$pid.'.pid')) unlink($dir.DIRECTORY_SEPARATOR.$pid.'.pid');
            $pid = null;
        }else{ // 写入pid
            \yii\helpers\FileHelper::createDirectory($dir);
            $pid = getmypid();
            file_put_contents($dir.DIRECTORY_SEPARATOR.$pid.'.pid', $pid);
        }
        
        return $pid;
    }
    
    /**
     * 记录当前脚本执行次数
     */
    private function _increase()
    {
        $act = $this->action->id;
        if(!isset($this->cacheRuns[$act])) $this->cacheRuns[$act] = 0;
        $this->cacheRuns[$act] += 1;
        return $this->cacheRuns[$act];
    }
}
