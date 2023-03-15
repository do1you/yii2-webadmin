<?php

/**
 * 读取和导出excel
 * @author 统一
 *
 */

namespace webadmin\ext;

use Yii;

class PhpCsv
{
    /**
     * 读取csv  
     */
    public static function readfile($file = '', $sheet = 0, $columnCnt = 0, &$options = [])
    {
    }

    /**
     * 导出csv
     */
    public static function export($model, \yii\data\BaseDataProvider $dataProvider, $titles = [], $filename = null, $options = [])
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');


        // 输出文件
        if (!$filename) $filename = date('YmdHis');
        else $filename = str_replace(array(":", "-"), "_", $filename);
        if (preg_match("/cli/i", php_sapi_name()) || !empty($options['return'])) { // cli模式，异步导出EXCEL
            $savePath = Yii::getAlias((isset($options['save_path']) ? trim($options['save_path'], '/') . '/' : '@runtime/excels/') . $filename . ".csv");
            if (stristr(PHP_OS, 'WIN')) {
                $encode = stristr(PHP_OS, 'WIN') ? 'GBK' : 'UTF-8';
                $savePath = iconv('UTF-8', $encode, $savePath); // 转码适应不同操作系统的编码规则
            }
            \yii\helpers\FileHelper::createDirectory(dirname($savePath));
            $fp = fopen($savePath, "w");
        } else { // 正常模式
            header('Content-Type: text/csv');
            header('Content-Disposition:attachment;filename="' . $filename . '.csv"');
            $fp = fopen('php://output', 'w');
        }
        ob_clean();
        ob_start();
        fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        self::writeCsv($fp, $model, $dataProvider, $titles, $options);
        ob_end_flush();
        if (preg_match("/cli/i", php_sapi_name()) || !empty($options['return'])) { // cli模式，异步导出csv
            return $savePath;
        } else {
            exit;
        }
    }

    /**
     * 根据model\titles写入工作表
     */
    public static function writeCsv($fp, $model, \yii\data\BaseDataProvider $dataProvider, $titles = [], $options = [])
    {
        $modelClass = $dataProvider->query->modelClass;
        $model = $modelClass ? $modelClass::model() : null; // 数据模型
        $titles = $titles ? $titles : ($model ? $model->attributeLabels() : array_keys($dataProvider->query->one()->attributes)); // 标题
        $count = $dataProvider->query->count(); // 总记录数

        $row = 1;
        $totalRow = [];
        // 标题
        if ($titles) {
            $row_titles = [];
            foreach ($titles as $tkey => $tval) {
                $attribute = is_array($tval) ? (isset($tval['attribute']) ? $tval['attribute'] : null) : $tval;
                $attribute = $attribute && is_string($attribute) ? $attribute : $tkey;
                $label = $model ? $model->getAttributeLabel($attribute) : $attribute;
                $row_titles[] = $label;
            }
            fputcsv($fp, $row_titles);
        }

        // 数据，分批量查询数据
        foreach ($dataProvider->query->batch() as $data) {
            foreach ($data as $item) {
                $index = 0;
                $row_values = [];
                foreach ($titles as $tkey => $tval) {
                    $attribute = is_array($tval) ? (isset($tval['attribute']) ? $tval['attribute'] : null) : $tval;
                    $attribute = $attribute && is_string($attribute) ? $attribute : $tkey;
                    if (!is_string($tval) && is_callable($tval)) {
                        $value = call_user_func($tval->bindTo($item), $item, $index, $row);
                    } elseif (!empty($tval['value'])) {
                        if (is_string($tval['value'])) {
                            $value = \yii\helpers\ArrayHelper::getValue($item, $tval['value']);
                        } elseif (is_callable($tval['value'])) {
                            $value = call_user_func($tval['value']->bindTo($item), $item, $index, $row);
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
                    $row_values[] = $value . "\t";
                }
                fputcsv($fp, $row_values);
                $row++;
            }
        }

        // 汇总
        if ($totalRow) {
            $index = 0;
            $row_totals = [];
            foreach ($titles as $tkey => $tval) {
                $attribute = is_array($tval) ? (isset($tval['attribute']) ? $tval['attribute'] : null) : $tval;
                $attribute = $attribute && is_string($attribute) ? $attribute : $tkey;
                $let = \webadmin\ext\Helpfn::intToChr($index++);
                if (isset($totalRow[$attribute])) {
                    $totalRow[$attribute] = round($totalRow[$attribute] * 1000) / 1000;
                    $row_totals[] = $totalRow[$attribute];
                    //echo $let.$row."=>".$totalRow[$attribute]."\r\n";
                } else {
                    $row_totals[] = '';
                }
            }
            fputcsv($fp, $row_values);
            $row++;
        }

        return true;
    }

    /**
     * 异步后台生成csv文档，控制器方法需要返回生成的文档路径
     * 路由，SESSION数据，GET数据，POST数据
     */
    public static function consoleExport($route = '', $session = '', $get = '', $post = '')
    {
        // $route = "settlement/set-bank/index";
        // $session = "__id=1&set-user-balance-log%5B0%5D=set-user%2Fview&set-user-balance-log%5Bid%5D=1097&set-user-balance-log%5B%23%5D=tab_balance_log&set-user-balance%5B0%5D=set-user%2Fview&set-user-balance%5Bid%5D=1097&set-user-balance%5B%23%5D=tab_balance";
        // $get = "r=%2F%2Fsettlement%2Fset-bank%2Findex.html&SetBank%5Buser_id%5D=&SetBank%5Bbank_type%5D=&SetBank%5Bchannel_id%5D=&SetBank%5Bbank_no%5D=&SetBank%5Bbank_name%5D=&SetBank%5Bbinding_mobile%5D=&SetBank%5Bis_credit%5D=&SetBank%5Bis_company%5D=&SetBank%5Bstate%5D=&SetBank%5Bis_enterprise%5D=&SetBank%5Bview_balance%5D=&SetBank%5Bys_status%5D=&export_type=1";
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
        $cacheName = self::exportCacheName($route, $session, $get, $post);
        Yii::$app = $app;

        if (!$path || !file_exists($path)) return false;
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
                $list = \yii\helpers\ArrayHelper::map(\webadmin\modules\config\models\SysQueue::find()->where(['user_id' => $uid, 'callback' => 'csv'])->all(), 'id', 'v_self', 'state');
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
                $uid && \webadmin\modules\config\models\SysQueue::deleteAll("user_id='{$uid}' and state='2' and taskphp='daemon/excel/csv-export' and (params like :params or params like :params1)", [
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

        \webadmin\modules\config\models\SysQueue::queue('daemon/excel/csv-export', [$route, $session, $get, $post], ['callback' => 'csv']);

        Yii::$app->response->redirect($url);
        Yii::$app->end();
    }

    /**
     * 异步生成csv缓存名
     */
    public static $identParams = '';
    public static function exportCacheName($route = '', $session = '', $get = '', $post = '')
    {
        $uid = ((Yii::$app instanceof \yii\web\Application && Yii::$app->user->id) ? Yii::$app->user->id : '');
        $idParam = Yii::$app->has('user') ? Yii::$app->user->idParam : '';
        $uid = $uid ? $uid : ($idParam&&isset($session[$idParam]) ? $session[$idParam] : 'notuser');
        return $route.'/'.$uid.'/'.md5($get).(self::$identParams ? '/'.md5(self::$identParams) : '');
    }
}
