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
    public $maxProcessNum = 5000;
    
    /**
     * 是否windows系统
     */
    public $isWindows = false;
    
    /**
     * 后台监听进程
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
        $this->processPath = Yii::getAlias('@vendor/../');
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
                $this->actionHelp();
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
            return 0;
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
            usleep(400000);
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
		    $this->actionStop();
		    $this->actionStart();
		}else{
		    $pid = $this->_processes();
		    $state = $this->ansiFormat('is started', Console::FG_RED);
		    Console::output("processes {$this->processName} {$state}({$pid}).");
		}
		
        return 0;
    }
    
    /**
     * 帮助
     */
    public function actionHelp()
    {
        Console::output("command list: start | stop | restart | rstart | status | help.");
    }
    
    /**
     * 执行命令
     */
    private function _run($cmd = '')
    {
        $dir = dirname($this->pidFile).DIRECTORY_SEPARATOR.'logs';
        \yii\helpers\FileHelper::createDirectory($dir);
        $path = $dir . DIRECTORY_SEPARATOR . date('Ymd').'.log';
        $cmd = $this->isWindows
        ? 'start /b wmic process call create "'.$this->processPath.'yii.bat '.$cmd.' >> ' . $path . ' >nul" | find "ProcessId"'// >nul
        : 'nohup '.$this->processPath.'yii '.$cmd.' >> '. $path .' 2>&1 &'; // 2>&1
        $handle = popen($cmd, 'r');
        if($this->isWindows){
            $read = fread($handle, 200);
            $read = preg_replace('/\D/s', '', $read);
        }else{
            $read = 0;
        }
        @pclose($handle);
        return $read;
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
        SysCrontab::listen($this->maxProcessNum);
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
        SysQueue::listen($this->maxProcessNum);        
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
