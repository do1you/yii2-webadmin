<?php

/**
 * 读取和导出excel
 * @author 统一
 *
 */

namespace webadmin\ext;

use Yii;

class PhpExcel
{
    /**
     * 读取excel
     */
    public static function readfile($file = '', $sheet = 0, $columnCnt = 0, &$options = [])
    {
        try {
            if (empty($file) || !file_exists($file)) {
                $file = iconv('UTF-8', 'GBK', $file);
                if (empty($file) || !file_exists($file)) {
                    throw new \yii\web\HttpException(200, Yii::t('common', '文件不存在!'));
                }
            }

            $objRead = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');

            if (!$objRead->canRead($file)) {
                $objRead = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xls');

                if (!$objRead->canRead($file)) {
                    throw new \yii\web\HttpException(200, Yii::t('common', '只支持导入Excel文件！'));
                }
            }

            /* 如果不需要获取特殊操作，则只读内容，可以大幅度提升读取Excel效率 */
            empty($options) && $objRead->setReadDataOnly(true);

            $obj = $objRead->load($file);
            $currSheet = $obj->getSheet($sheet);

            if (isset($options['mergeCells'])) {
                /* 读取合并行列 */
                $options['mergeCells'] = $currSheet->getMergeCells();
            }

            if (0 == $columnCnt) {
                /* 取得最大的列号 */
                $columnH = $currSheet->getHighestColumn();
                /* 兼容原逻辑，循环时使用的是小于等于 */
                $columnCnt = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($columnH);
            }

            /* 获取总行数 */
            $rowCnt = $currSheet->getHighestRow();
            $data   = [];

            /* 读取内容 */
            for ($_row = 1; $_row <= $rowCnt; $_row++) {
                $isNull = true;

                for ($_column = 1; $_column <= $columnCnt; $_column++) {
                    $cellName = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($_column);
                    $cellId   = $cellName . $_row;
                    $cell     = $currSheet->getCell($cellId);

                    if (isset($options['format'])) {
                        /* 获取格式 */
                        $format = $cell->getStyle()->getNumberFormat()->getFormatCode();
                        /* 记录格式 */
                        $options['format'][$_row][$cellName] = $format;
                    }

                    if (isset($options['formula'])) {
                        /* 获取公式，公式均为=号开头数据 */
                        $formula = $currSheet->getCell($cellId)->getValue();

                        if (0 === strpos($formula, '=')) {
                            $options['formula'][$cellName . $_row] = $formula;
                        }
                    }

                    if (isset($format) && 'm/d/yyyy' == $format) {
                        /* 日期格式翻转处理 */
                        $cell->getStyle()->getNumberFormat()->setFormatCode('yyyy/mm/dd');
                    }

                    $data[$_row][$cellName] = trim($currSheet->getCell($cellId)->getFormattedValue());

                    if (!empty($data[$_row][$cellName])) {
                        $isNull = false;
                    }
                }

                /* 判断是否整行数据为空，是的话删除该行数据 */
                if ($isNull) {
                    unset($data[$_row]);
                }
            }

            return $data;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * 导出excel
     */
    public static function export($model, \yii\data\DataProviderInterface $dataProvider, $titles = [], $filename = null, $options = [])
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet = self::writeSheet($sheet, $model, $dataProvider, $titles, $options);

        // 多个工作表
        if (!empty($options['sheets']) && is_array($options['sheets'])) {
            $sheets = isset($options['sheets']['dataProvider']) ? [$options['sheets']] : $options['sheets'];
            foreach ($sheets as $sheetItem) {
                if (isset($sheetItem['model']) && isset($sheetItem['dataProvider']) && isset($sheetItem['titles'])) {
                    $newSheet = $spreadsheet->createSheet();
                    $newOptions = array_merge($options, (!empty($sheetItem['options']) ? $sheetItem['options'] : []));
                    $newSheet = self::writeSheet($newSheet, $sheetItem['model'], $sheetItem['dataProvider'], $sheetItem['titles'], $newOptions);

                    // 工作表名称
                    if (isset($sheetItem['title'])) {
                        $newSheet->setTitle($sheetItem['title']);
                    }
                }
            }
        }

        // 输出文件
        if (!$filename) $filename = date('YmdHis');
        else $filename = str_replace(array(":", "-"), "_", $filename);
        if (preg_match("/cli/i", php_sapi_name()) || !empty($options['return'])) { // cli模式，异步导出EXCEL
            $savePath = Yii::getAlias((isset($options['save_path']) ? trim($options['save_path'], '/') . '/' : '@runtime/excels/') . $filename . ".xlsx");
            if (stristr(PHP_OS, 'WIN')) {
                $encode = stristr(PHP_OS, 'WIN') ? 'GBK' : 'UTF-8';
                $savePath = iconv('UTF-8', $encode, $savePath); // 转码适应不同操作系统的编码规则
            }
            \yii\helpers\FileHelper::createDirectory(dirname($savePath));
        } else { // 正常模式
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition:attachment;filename="' . $filename . '.xlsx"');
            header("Content-Transfer-Encoding: binary");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
            header("Pragma: no-cache");
            $savePath = 'php://output';
        }
        ob_clean();
        ob_start();
        $objWriter = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
        $objWriter->save($savePath);
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        ob_end_flush();
        if (preg_match("/cli/i", php_sapi_name()) || !empty($options['return'])) { // cli模式，异步导出EXCEL
            return $savePath;
        } else {
            exit;
        }
    }

    /**
     * 根据model\titles写入工作表
     */
    public static function writeSheet(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, $model, \yii\data\DataProviderInterface $dataProvider, $titles = [], $options = [])
    {
        $sheet->getStyle('A:AZ')->applyFromArray([
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        $modelClass = $dataProvider->hasProperty('query') && $dataProvider->query->hasProperty('modelClass') ? $dataProvider->query->modelClass : '';
        $model = $modelClass ? $modelClass::model() : null; // 数据模型
        $titles = $titles ? $titles : ($model ? $model->attributeLabels() : array_keys($dataProvider->hasProperty('query') ? $dataProvider->query->one()->attributes : [])); // 标题
        $dataProvider->setPagination(false);
        $count = $dataProvider->getCount(); // 总记录数

        $row = 1;
        $totalRow = [];

        // 工作表名称
        if (isset($options['title'])) {
            $sheet->setTitle($options['title']);
        }

        // 合并栏位，仅支持二级
        if (!empty($options['colspans']) && is_array($options['colspans'])) {
            $index = 0;
            $colspans = $options['colspans'];
            foreach ($titles as $tkey => $tval) {
                $let = \webadmin\ext\Helpfn::intToChr($index++);

                $attribute = is_array($tval) ? (isset($tval['attribute']) ? $tval['attribute'] : $tkey) : $tval;
                $attributeValue = (!empty($tval['value']) && is_string($tval['value']) ? $tval['value'] : $attribute);
                $attributeValue = $attributeValue ? explode(".", $attributeValue) : [];
                $attributeValue = $attributeValue ? end($attributeValue) : '';

                if (isset($colspans[$attribute]) || isset($colspans[$attributeValue])) {
                    $begin = isset($colspans[$attribute]) ? $attribute : $attributeValue;
                    $start = $let . $row;
                }

                if (!empty($begin)) {
                    $sheet->setCellValueExplicit($let . $row, $colspans[$begin]['label'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    if ($colspans[$begin]['attribute'] == $attribute || $colspans[$begin]['attribute'] == $attributeValue) {
                        unset($begin);
                        $end = $let . $row;
                        if ($start != $end) {
                            $sheet->mergeCells("{$start}:{$end}");  // 横向合并
                            $sheet->getRowDimension($row)->setRowHeight(60);
                            $sheet->getStyle($start)->getAlignment()->setWrapText(true);
                        }
                    }
                } else {
                    $start = $let . $row;
                    $end = $let . ($row + 1);
                    $label = $model ? $model->getAttributeLabel($attribute) : $attribute;
                    $sheet->setCellValueExplicit($start, $label, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    $sheet->mergeCells("{$start}:{$end}");  // 竖向合并
                }
            }
            $row++;
        }

        // 标题
        if ($titles) {
            $index = 0;
            foreach ($titles as $tkey => $tval) {
                $attribute = is_array($tval) ? (isset($tval['label']) ? $tval['label'] : (isset($tval['attribute']) ? $tval['attribute'] : null)) : $tval;
                $attribute = $attribute && is_string($attribute) ? $attribute : $tkey;
                $label = $model ? $model->getAttributeLabel($attribute) : $attribute;
                $let = \webadmin\ext\Helpfn::intToChr($index++);
                $sheet->setCellValueExplicit($let . $row, $label, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                //$sheet->getColumnDimension($let)->setWidth(max(strlen($label),3));
                $sheet->getColumnDimension($let)->setAutoSize(true);
                //echo $let.$row."=>".$label."\r\n";
            }
            //$sheet->getRowDimension($row)->setRowHeight(40);
            $row++;
        }

        // 数据，分批量查询数据
        foreach ((($dataProvider->hasProperty('query') && $model !== null) ? $dataProvider->query->batch(100, $dataProvider->db) : [$dataProvider->getModels()]) as $data) {
            foreach ($data as $item) {
                $index = 0;
                foreach ($titles as $tkey => $tval) {
                    $attribute = is_array($tval) ? (isset($tval['attribute']) ? $tval['attribute'] : null) : $tval;
                    $attribute = $attribute && is_string($attribute) ? $attribute : $tkey;
                    if (!is_string($tval) && is_callable($tval)) {
                        $value = call_user_func($tval->bindTo($item), $item, $index, $row);
                    } elseif (!empty($tval['value'])) {
                        if (is_string($tval['value'])) {
                            $value = \yii\helpers\ArrayHelper::getValue($item, $tval['value']);
                        } elseif (is_callable($tval['value'])) {
                            $value = is_object($item) ? call_user_func($tval['value']->bindTo($item), $item, $index, $row) : call_user_func($tval['value'], $item, $index, $row);
                        } else {
                            $value = $tval['value'];
                        }
                    } elseif ($attribute !== null) {
                        $value = \yii\helpers\ArrayHelper::getValue($item, $attribute);
                    } elseif ($tkey !== null) {
                        $value = \yii\helpers\ArrayHelper::getValue($item, $tkey);
                    }

                    if (is_numeric($value) && (empty($options['skip_total']) || (is_array($options['skip_total']) && !in_array($attribute, $options['skip_total'])))) { // 汇总
                        if (!isset($totalRow[$attribute])) $totalRow[$attribute] = 0;
                        $totalRow[$attribute] += $value;
                    }

                    $let = \webadmin\ext\Helpfn::intToChr($index++);

                    //匹配到颜色参数，设置颜色
                    if (preg_match("/color:(\w*);$/", $value, $matches)) {
                        $value = str_replace($matches[0], '', $value);
                        $sheet->getStyle($let . $row)->getFont()->getColor()->setARGB($matches[1]);
                    }
                    if (preg_match("/^\d{8,50}$/", $value) || (preg_match("/^\d{2,50}$/", $value) && substr($value, 0, 1) == '0')) {
                        $sheet->setCellValueExplicit($let . $row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING); // setCellValueExplicit
                    } else {
                        $sheet->setCellValue($let . $row, $value);
                    }
                    //echo $let.$row."=>".$value."\r\n";
                }
                $row++;
            }
        }

        // 汇总
        if ($totalRow) {
            $index = 0;
            foreach ($titles as $tkey => $tval) {
                $attribute = is_array($tval) ? (isset($tval['attribute']) ? $tval['attribute'] : null) : $tval;
                $attribute = $attribute && is_string($attribute) ? $attribute : $tkey;
                $let = \webadmin\ext\Helpfn::intToChr($index++);
                if (isset($totalRow[$attribute])) {
                    $totalRow[$attribute] = round($totalRow[$attribute] * 1000) / 1000;
                    $sheet->setCellValueExplicit($let . $row, ($index == 1 ? '总计：' : $totalRow[$attribute]), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    //echo $let.$row."=>".$totalRow[$attribute]."\r\n";
                }
            }
            $row++;
        }

        return $sheet;
    }

    /**
     * 异步后台生成excel文档，控制器方法需要返回生成的文档路径
     * 路由，SESSION数据，GET数据，POST数据
     */
    public static function consoleExport($route = '', $session = '', $get = '', $post = '')
    {
        // 解析WEB基础参数
        $get && (is_string($get) ? parse_str($get, $_GET) : ($_GET = array_merge($_GET, $get)));
        $post && (is_string($post) ? parse_str($post, $_POST) : ($_POST = array_merge($_POST, $post)));
        $session && (is_string($session) ? parse_str($session, $_SESSION) : ($_SESSION = array_merge($_SESSION, $session)));
        $_GET['is_export'] = '1';

        // SERVER参数
        $_SERVER['REMOTE_ADDR'] = $_SERVER['SERVER_ADDR'] = $_SERVER['SERVER_NAME'] = $_SERVER['HTTP_HOST'] = '127.0.0.1';
        $_SERVER['QUERY_STRING'] = $_SERVER['REDIRECT_QUERY_STRING'] = $get;
        $_SERVER['REQUEST_URI'] = '/' . $route . '?' . $get;
        defined('DEBUG_USER') or define('DEBUG_USER', true);

        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        // 运行
        $config = yii\helpers\ArrayHelper::merge(
            require Yii::getAlias('@app/config/main.php'),
            require Yii::getAlias('@app/config/main-local.php')
        );
        $app = Yii::$app;
        new \yii\web\Application($config);
        Yii::setAlias('@webroot', Yii::getAlias('@app/web'));
        //Yii::setAlias('@web', '/');
        Yii::$app->request->pathInfo = strtolower(preg_replace('/(?<=[a-z])([A-Z])/', '-$1', Yii::$app->request->pathInfo)); // 兼容大写路由地址
        $idParam = Yii::$app->user->idParam;
        if (($uid = (isset($_SESSION[$idParam]) ? $_SESSION[$idParam] : ''))) {
            $nModel = \webadmin\modules\authority\models\AuthUser::findIdentity($uid);
            Yii::$app->user->login($nModel, 86400);
        }
        $path = Yii::$app->runAction($route);
        Yii::$app = $app;

        if (!$path || !file_exists($path)) return false;
        $cacheName = self::exportCacheName($route, $session, $get, $post);
        Yii::$app->cache->set($cacheName, $path);

        return $path;
    }

    /**
     * 异步生成EXCEL入口
     * 加入缓存的条件参数，如选中的门店等; 跳回的URL地址，默认上一个页面
     */
    public static function beginExport($url = '')
    {
        $uid = ((Yii::$app instanceof \yii\web\Application && Yii::$app->user->id) ? Yii::$app->user->id : '');
        if (preg_match("/cli/i", php_sapi_name()) || !($is_export = Yii::$app->request->get('is_export'))) { // cli模式  || (defined('YII_DEBUG')&&YII_DEBUG)
            if (empty($is_export) && $uid) {
                // 提醒有多少个EXCEL文档正在下载
                $list = \yii\helpers\ArrayHelper::map(\webadmin\modules\config\models\SysQueue::find()->where(['user_id' => $uid, 'callback' => 'excel'])->all(), 'id', 'v_self', 'state');
                if (!empty($list['2']) && is_array($list['2'])) {
                    foreach ($list['2'] as $item) {
                        $params = is_array($item['params']) ? $item['params'] : json_decode($item['params'], true);
                        if (!empty($params[0])) {
                            $downUrl = \yii\helpers\Url::to(['/' . $params[0]]) . '?' . (!empty($params[2]) ? $params[2] . '&' : '') . 'is_export=2';
                            $message = "您于{$item['create_time']}下载的文档已经生成，请 <a href='{$downUrl}' class='orange'>点击这里下载</a>。";
                            Yii::$app->session->setFlash('info exceldown', $message);
                        } else {
                            $item->delete();
                        }
                    }
                }
                if (!empty($list['3']) && is_array($list['3'])) {
                    foreach ($list['3'] as $item) $item->delete();
                    $message = "您有" . count($list['3']) . "个文档已经生成失败，请您进入对应的菜单重新进行下载。";
                    Yii::$app->session->setFlash('warning exceldown', $message);
                }
                if (!empty($list['0']) || !empty($list['1'])) {
                    $message = "您有" . count($list['0']) . "个文档正在排队中，" . count($list['1']) . "个文档正在后台生成中，请喝杯咖啡耐心等待。";
                    Yii::$app->session->setFlash('info exceldown', $message);
                    $url = \yii\helpers\Url::to(['/config/default/down', 'uid' => $uid]);
                    $script = <<<eot
                    	var timeobj,fn = function(){
                    		$.ajax({
                    			url: '{$url}',
                    			dataType: 'json',
                    			success : function(json) {
                    				if(json && json.msg && json.url){
                    					bootbox.confirm(json.msg, function(result) {
                    						if(result) {
                    							location.href = json.url;
                    						}
                    					});
                    					timeobj && clearInterval(timeobj);
                    				}
                    			}
                    		});
                    	};
                    	fn();
                    	timeobj = setInterval(fn,5000);
eot;
                    Yii::$app->controller->view && Yii::$app->controller->view->registerJs($script);
                }
            }
            return true;
        }

        // 组装参数
        $url = $url ? $url : $_SERVER['HTTP_REFERER'];
        $route = trim(Yii::$app->request->pathInfo, ".html");
        unset($_GET['is_export']);
        $get = http_build_query($_GET);
        $post = http_build_query($_POST);
        $session = http_build_query($_SESSION);
        $cacheName = self::exportCacheName($route, $session, $get, $post);
        $filePath = Yii::$app->cache->get($cacheName);

        if ($filePath && file_exists($filePath)) { // 文件已存在
            $cacheFile = preg_replace('/^.+[\\\\\\/]/', '', $filePath);
            if (stristr(PHP_OS, 'WIN')) {
                $encode = stristr(PHP_OS, 'WIN') ? 'GBK' : 'UTF-8';
                $cacheFile = iconv($encode, 'UTF-8', $cacheFile); // 转码适应不同操作系统的编码规则
            }
            if ($is_export == '3') { // 重新下载
                unlink($filePath);
                Yii::$app->cache->delete($cacheName);
            } elseif ($is_export == '2') { // 直接下载
                $uid && \webadmin\modules\config\models\SysQueue::deleteAll("user_id='{$uid}' and state='2' and taskphp='daemon/excel/export' and (params like :params or params like :params1)", [
                    ':params' => '%' . addcslashes(addcslashes($route, '/'), '/') . '%',
                    ':params1' => '%' . addcslashes($route, '/') . '%',
                ]);
                header("Content-Type: application/octet-stream");
                header('Content-Disposition:inline;filename="' . $cacheFile . '"');
                header("Content-Transfer-Encoding: binary");
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Pragma: no-cache");
                echo file_get_contents($filePath);
                exit;
            } else { // 询问下载
                $time = filectime($filePath);
                $time = $time ? date('Y-m-d H:i:s', $time) : "";
                $reurl = Yii::$app->request->url;
                $downUrl = $reurl . (strstr($reurl, '?') === false ? '?' : '&') . "is_export=2";
                $reloadUrl = $reurl . (strstr($reurl, '?') === false ? '?' : '&') . "is_export=3";
                $message = "【{$cacheFile}】文件已" . ($time ? "于{$time}" : "经") . "生成，您可以 <a href='{$downUrl}' class='red'><strong>直接下载</strong></a> 或者 <a href='{$reloadUrl}' class='yellow'><strong>重新下载</strong></a>";
                Yii::$app->session->setFlash('info exceldown', $message);
                Yii::$app->response->redirect($url);
                Yii::$app->end();
            }
        }

        \webadmin\modules\config\models\SysQueue::queue('daemon/excel/export', [$route, $session, $get, $post], ['callback' => 'excel']);

        Yii::$app->response->redirect($url);
        Yii::$app->end();
    }

    /**
     * 异步生成EXCEL缓存名
     */
    public static $identParams = '';
    public static function exportCacheName($route='',$session='',$get='',$post='')
    {
        $uid = ((Yii::$app instanceof \yii\web\Application && Yii::$app->user->id) ? Yii::$app->user->id : '');
        $idParam = Yii::$app->has('user') ? Yii::$app->user->idParam : '';
        $uid = $uid ? $uid : ($idParam&&isset($session[$idParam]) ? $session[$idParam] : 'notuser');
        return $route.'/'.$uid.'/'.(md5($get)).(self::$identParams ? '/'.md5(self::$identParams) : '');
    }
}
