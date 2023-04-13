<?php
/**
 * 计划任务入口继承父类，只能补继承
 */
namespace webadmin\console;

use Yii;

defined('YII_BEGIN_TIME') or define('YII_BEGIN_TIME', microtime(true));

abstract class CController extends \yii\console\Controller
{
    /**
     * 执行结果描述
     */
    protected $message = '';
    
    /**
     * 初始化
     */
    public function init()
    {
        set_time_limit(300);
        ini_set('memory_limit', '256M');
    }
    
    /**
     * 执行前
     */
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }
    
    // 执行后
    public function afterAction($action, $result)
    {
        // 记录计划任务日志
        \webadmin\modules\logs\models\LogCrontab::insertion([
            'command' => (!($this->module instanceof \yii\base\Application) ? $this->module->id.'/' : '').$this->id,
            'action' => $action->id,
            'args' => trim(trim(json_encode(Yii::$app->requestedParams),'['),']'),
            'exit_code' => (strlen($result) ? $result : '0'),
            'message' => $this->message,
            'starttime' => date('Y-m-d H:i:s', floor(YII_BEGIN_TIME)),
            'endtime' => date('Y-m-d H:i:s'),
            'run_millisec' => round((microtime(true) - YII_BEGIN_TIME)*1000),
            'user_id' => '0',
        ]);
        
        return parent::afterAction($action, $result);
    }
    
    /**
     * @var bool
     * 是否启动调试模式
     */
    public $dev;
    public function options($actionID)
    {
        $params = parent::options($actionID);
        $params[] = 'dev';
        return $params;
    }
}