<?php
namespace webadmin\modules\logs\console;

use Yii;
use yii\helpers\Console;
use webadmin\modules\config\models\SysConfig;

class LogsCrontabController extends \webadmin\console\CController
{
    /**
     * 删除日志
     */
    public function actionRemoveLog()
    {
        // 当前时间
        $time = strtotime(date('Y-m-d'));
        $this->message = [];
        
        // 删除接口请求日志
        $rtime = date('Y-m-d H:i:s',$time - intval(SysConfig::config('logs_api_request_day', 30))*3600*24);
        $num = \webadmin\modules\logs\models\LogApiRequest::deleteAll("create_time<='{$rtime}'");
        $this->message[] = "删除了{$num}条接口请求日志";
        
        // 删除接口访问日志
        $rtime = date('Y-m-d H:i:s',$time - intval(SysConfig::config('logs_api_response_day', 30))*3600*24);
        $num = \webadmin\modules\logs\models\LogApiResponse::deleteAll("create_time<='{$rtime}'");
        $this->message[] = "删除了{$num}条接口访问日志";
        
        // 删除计划任务日志
        $rtime = date('Y-m-d H:i:s',$time - intval(SysConfig::config('logs_crontab_day', 30))*3600*24);
        $num = \webadmin\modules\logs\models\LogCrontab::deleteAll("starttime<='{$rtime}'");
        $this->message[] = "删除了{$num}条计划任务日志";
        
        // 删除数据库日志
        $rtime = date('Y-m-d H:i:s',$time - intval(SysConfig::config('logs_database_day', 30))*3600*24);
        $num = \webadmin\modules\logs\models\LogDatabase::deleteAll("create_time<='{$rtime}'");
        $this->message[] = "删除了{$num}条数据库日志";
        
        // 删除登录日志
        $rtime = date('Y-m-d H:i:s',$time - intval(SysConfig::config('logs_login_day', 30))*3600*24);
        $num = \webadmin\modules\logs\models\LogUserLogin::deleteAll("addtime<='{$rtime}'");
        $this->message[] = "删除了{$num}条登录日志";
        
        // 删除短信日志
        $rtime = date('Y-m-d H:i:s',$time - intval(SysConfig::config('logs_sms_day', 30))*3600*24);
        $num = \webadmin\modules\logs\models\LogSms::deleteAll("addtime<='{$rtime}'");
        $this->message[] = "删除了{$num}条短信日志";
        
        // 删除操作日志
        $rtime = date('Y-m-d H:i:s',$time - intval(SysConfig::config('logs_user_action_day', 30))*3600*24);
        $num = \webadmin\modules\logs\models\LogUserAction::deleteAll("addtime<='{$rtime}'");
        $this->message[] = "删除了{$num}条操作日志";
        
        $this->message = implode("\n",$this->message);
        echo $this->message;
        
        return 0;
    }
}