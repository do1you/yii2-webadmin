<?php
/**
 * 计划任务入口继承父类，只能补继承
 */
namespace webadmin\console;

use Yii;

abstract class CController extends \yii\console\Controller
{
    /**
     * 开始执行时间
     */
    private $_startTime;
    
    /**
     * 执行结果描述
     */
    protected $message = '';
    
    /**
     * 初始化
     */
    public function init()
    {
    }
    
    /**
     * 执行前
     */
    public function beforeAction($action)
    {
        $this->_startTime = date('Y-m-d H:i:s');
        
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
            'starttime' => $this->_startTime,
            'endtime' => date('Y-m-d H:i:s'),
            'user_id' => '0',
        ]);
        
        return parent::afterAction($action, $result);
    }
    
    // 命令行调试参数
    protected $dev;
    public function options($actionID)
    {
        $params = parent::options($actionID);
        $params[] = 'dev';
        return $params;
    }
}