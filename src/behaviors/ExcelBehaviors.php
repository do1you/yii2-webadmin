<?php

/**
 * 触发EXCEL异步执行
 */

namespace webadmin\behaviors;

use Yii;
use yii\web\Controller;

class ExcelBehaviors extends \yii\base\Behavior
{
    /**
     * 是否后台生成EXCEL，默认是
     */
    public $isConsoleExport = true;

    /**
     * 异步生成缓存标识（除GET和POST之外）
     */
    public $identParams = [];


    /**
     * 行为触发的事件
     */
    public function events()
    {
        return [Controller::EVENT_BEFORE_ACTION => 'beforeAction'];
    }

    /**
     * 判断权限
     */
    public function beforeAction($event)
    {
        // 开启系统调试模式或者用户调试模式
        if ((defined('YII_DEBUG') && YII_DEBUG) || (defined('DEBUG_USER') && DEBUG_USER)) {
            return true;
        }

        $url = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "";
        $url = $url ? $url : Yii::$app->request->baseUrl;
        if (!empty(Yii::$app->request->get('export_type')) && Yii::$app->request->get('export_type') == 1) {
            //如果设置了export_type 则导出为csv
            \webadmin\ext\PhpCsv::$identParams = $this->identParams;
            \webadmin\ext\PhpCsv::beginExport($url);
        } else {
            \webadmin\ext\PhpExcel::$identParams = $this->identParams;
            \webadmin\ext\PhpExcel::beginExport($url);
        }

        return true;
    }
}
