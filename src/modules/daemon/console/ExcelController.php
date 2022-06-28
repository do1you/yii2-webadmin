<?php
/**
 * 异步生成EXCEL文档
 */
namespace webadmin\modules\daemon\console;

use Yii;

class ExcelController extends \webadmin\console\CController
{
    /**
     * 生成excel文档
     * yii daemon/excel/export
     */
    public function actionExport($route='',$session='',$get='',$post='')
    {
        $path = \webadmin\ext\PhpExcel::consoleExport($route,$session,$get,$post);
        $result = file_exists($path) ? 0 : 1;
        $this->message = "处理结果（{$result}）：{$path}";
        return $result;
    }
    /**
     * 生成csv文档
     * yii daemon/excel/csv-export
     */
    public function actionCsvExport($route='',$session='',$get='',$post='')
    {
        $path = \webadmin\ext\PhpCsv::consoleExport($route,$session,$get,$post);
        $result = file_exists($path) ? 0 : 1;
        $this->message = "处理结果（{$result}）：{$path}";
        return $result;
    }
}
