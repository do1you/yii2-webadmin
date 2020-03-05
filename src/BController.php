<?php
namespace webadmin;

use Yii;
use yii\web\Response;
use yii\base\Event;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\base\UserException;
use yii\base\Exception;
use yii\base\ErrorException;

abstract class BController extends \yii\web\Controller
{
    public $is_open_nav = true; // 是否开启左侧菜单
    public $body_class; // BODY样式    
    public $layout = 'console_layout'; // 布局文件
    public $currNav = []; // 当前导航位置
    public $currUrl = null; // 当前菜单位置
    
    public $pageTitle; // 页面标题
    public $keywords; // 页面关键字
    public $description; // 页面描述    
    
    /**
     * 当前授制器是否需要权限验证
     */
    public $isAccessToken = true;
    
    /**
     * 当前控制器中需要缓存查询条件的方法
     */
    public $searchCacheActions = ['index', 'list', 'tree'];
    
    // 初始化
    public function init()
    {
        // 输出事件监听
        Yii::$app->response->off(Response::EVENT_BEFORE_SEND);
        Yii::$app->response->off(Response::EVENT_AFTER_SEND);
        Yii::$app->response->on(Response::EVENT_BEFORE_SEND, [$this, 'beforeSend']);
        Yii::$app->response->on(Response::EVENT_AFTER_SEND, [$this, 'afterSend']);
        
        parent::init();
        
        // 定义组件
        Yii::$app->setComponents([
            // 资源组件
            'assetManager'=>[
                'class' => '\yii\web\AssetManager',
                'bundles'=>[
                    // 重置JQ的包
                    'yii\web\JqueryAsset'=>[
                        'sourcePath' => '@webadmin/themes/beyond/assets',
                        'js' => (Yii::$app->request->isAjax ? [] : ['js/jquery.min.js',]),
                    ],
                ]
            ],
        ]);
        
        // 定义别名路径
		list($assetPath, $assetUrl) = Yii::$app->getAssetManager()->publish('@webadmin/themes/beyond/assets');
        Yii::setAlias('@assetPath', $assetUrl);  
		Yii::setAlias('@assetUrl', $assetUrl);  
        
        // AJAX请求页面忽略布局文件
        Yii::$app->request->isAjax && ($this->layout = 'console_ajax');
        if(!empty($_REQUEST['layout'])) $this->layout = trim($_REQUEST['layout']);
        $this->layout = $this->layout ? '@webadmin/views/'.$this->layout : false;
    }
    
    // 执行前
    public function beforeAction($action)
    {
        return parent::beforeAction($action);
    }
    
    // 执行后
    public function afterAction($action, $result)
    {
        return parent::afterAction($action, $result);
    }    
    
    /**
     * 定义默认行为
     * {@inheritDoc}
     * @see \yii\rest\Controller::behaviors()
     */
    public function behaviors()
    {
        return [
            // 输出内容格式化
            'contentNegotiator' => [
                'class' => \yii\filters\ContentNegotiator::className(),
                'formats' => [
                    'text/html' => \yii\web\Response::FORMAT_HTML,
                    'application/xml' => \yii\web\Response::FORMAT_XML,
                    'application/json' => \yii\web\Response::FORMAT_JSON,
                ],
            ],
            // 权限判断
            'webAuthFilter' => [
                'class' => \webadmin\WebAuthFilter::className(),
                'isAccessToken' => $this->isAccessToken,
            ],
            // 缓存查询条件
            'searchBehaviors' => [
                'class' => \webadmin\SearchBehaviors::className(),
                'searchCacheActions' => $this->searchCacheActions,
            ],
        ];
    }
    
    /**
     * 读取excel
     */
    protected function importExecl($file = '', $sheet = 0, $columnCnt = 0, &$options = [])
    {
        try {
            /* 转码 */
            //$file = iconv("utf-8", "gbk", $file);
            
            if (empty($file) || !file_exists($file)) {
                throw new \yii\web\HttpException(200,Yii::t('common','文件不存在!'));
            }
            
            $objRead = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            
            if (!$objRead->canRead($file)) {
                $objRead = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xls');
                
                if (!$objRead->canRead($file)) {
                    throw new \yii\web\HttpException(200,Yii::t('common','只支持导入Excel文件！'));
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
                $columnCnt = Coordinate::columnIndexFromString($columnH);
            }
            
            /* 获取总行数 */
            $rowCnt = $currSheet->getHighestRow();
            $data   = [];
            
            /* 读取内容 */
            for ($_row = 1; $_row <= $rowCnt; $_row++) {
                $isNull = true;
                
                for ($_column = 1; $_column <= $columnCnt; $_column++) {
                    $cellName = Coordinate::stringFromColumnIndex($_column);
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
    protected function export($model, \yii\data\ActiveDataProvider $dataProvider, $titles = [], $filename = null, $options = [])
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        
        $modelClass = $dataProvider->query->modelClass;
        $model = $modelClass ? $modelClass::model() : null; // 数据模型
        $titles = $titles ? $titles : ($model ? $model->attributeLabels() : array_keys($dataProvider->query->one()->attributes)); // 标题
        $count = $dataProvider->query->count(); // 总记录数
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        $totalRow = [];
        
        // 父标题
        if(isset($options['parent_titles'])){
            // 预留
        }
        
        // 标题
        if($titles){
            $index = 0;
            foreach($titles as $tkey=>$tval){
                $attribute = is_array($tval) ? (isset($tval['attribute']) ? $tval['attribute'] : null) : $tval;
                $attribute = $attribute ? $attribute : $tkey;
                $label = $model ? $model->getAttributeLabel($attribute) : $attribute;
                $let = self::intToChr($index++);
                $sheet->setCellValueExplicit($let.$row, $label, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                //$sheet->getColumnDimension($let)->setWidth(max(strlen($label)*2,20,$sheet->getColumnDimension($let)->getWidth()));
                $sheet->getColumnDimension($let)->setAutoSize(true);
                //echo $let.$row."=>".$label."\r\n";
            }
            $row++;
        }

        // 数据，分批量查询数据
        foreach($dataProvider->query->batch() as $data)
        {
            foreach($data as $item){
                $index = 0;
                foreach($titles as $tkey=>$tval){
                    $attribute = is_array($tval) ? (isset($tval['attribute']) ? $tval['attribute'] : null) : $tval;
                    $attribute = $attribute ? $attribute : $tkey;
                    if(!empty($tval['value'])) {
                        if(is_string($tval['value'])) {
                            $value = \yii\helpers\ArrayHelper::getValue($item, $tval['value']);
                        }else{
                            $value = call_user_func($tval['value'], $item, $index, $row, $this);
                        }
                    }elseif($attribute !== null) {
                        $value = \yii\helpers\ArrayHelper::getValue($item, $attribute);
                    }elseif($tkey !== null) {
                        $value = \yii\helpers\ArrayHelper::getValue($item, $tkey);
                    }
                    
                    if(is_numeric($value) && (empty($options['skip_total']) || !in_array($attribute,$options['skip_total']))){ // 汇总
                        if(!isset($totalRow[$attribute])) $totalRow[$attribute] = 0;
                        $totalRow[$attribute] += $value;
                    }
                    
                    $let = self::intToChr($index++);
                    
                    if(preg_match("/^\d{8,50}$/",$value) || (preg_match("/^\d{2,50}$/",$value) && substr($value,0,1)=='0')){
                        $sheet->setCellValueExplicit($let.$row, $value, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING); // setCellValueExplicit
                    }else{
                        $sheet->setCellValue($let.$row, $value);
                    }
                    //echo $let.$row."=>".$value."\r\n";
                }
                $row++;
            }
        }
        
        // 汇总
        if($totalRow){
            $index = 0;
            foreach($titles as $tkey=>$tval){
                $attribute = is_array($tval) ? (isset($tval['attribute']) ? $tval['attribute'] : null) : $tval;
                $attribute = $attribute ? $attribute : $tkey;
                $let = self::intToChr($index++);
                if(isset($totalRow[$attribute])){
                    $totalRow[$attribute] = round($totalRow[$attribute]*1000)/1000;
                    $sheet->setCellValueExplicit($let.$row, $totalRow[$attribute], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
                    //echo $let.$row."=>".$totalRow[$attribute]."\r\n";
                }
            }
            $row++;
        }

        // 输出文件
        if(!$filename) $filename = date('YmdHis');
        else $filename = str_replace(array(":","-"),"_",$filename);
        if(preg_match("/cli/i", php_sapi_name())){ // cli模式，异步导出EXCEL
            $savePath = Yii::getAlias('@runtime/excels/'.(isset($options['save_path']) ? trim($options['save_path'],'/').'/' : '').$filename.".xlsx");
            $encode = stristr(PHP_OS, 'WIN') ? 'GBK' : 'UTF-8';
            $savePath = iconv('UTF-8', $encode, $savePath); // 转码适应不同操作系统的编码规则
            \yii\helpers\FileHelper::createDirectory(dirname($savePath));
        }else{ // 正常模式
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition:attachment;filename="'.$filename.'.xlsx"');
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
        exit;
    }
    
    /**
     * 输出前处理
     */
    public function beforeSend($event)
    {
        $response = $event->sender;
        if(Yii::$app->request->getHeaders()->get('X-Pjax') && $response->statusCode=='200'){
            // Pjax模式下Widgets最大最小化修复
            $response->content = $response->content . '<script>InitiateWidgets && InitiateWidgets();</script>';
        }
        
        // 记录数据库操作日志
        \webadmin\modules\logs\models\LogDatabase::logmodel()->saveLog();
        
        // 记录操作日志
        if(Yii::$app->user->id){
            $data = ['_GET'=>Yii::$app->request->get(),'_POST'=>Yii::$app->request->post()];
            if(empty($data['_GET'])) unset($data['_GET']);
            if(empty($data['_POST'])) unset($data['_POST']);
            \webadmin\modules\logs\models\LogUserAction::insertion([
                'remark' => ($this->currNav&&is_array($this->currNav) ? implode('-',$this->currNav) : ''),
                'action' => (!($this->module instanceof \yii\base\Application) ? $this->module->id.'/' : '').$this->id.'/'.$this->action->id,
                'request' => ($data ? print_r($data,true) : ""),
                'addtime' => date('Y-m-d H:i:s'),
                'ip' => Yii::$app->request->userIP,
                'user_id' => (Yii::$app->user->id ? Yii::$app->user->id : 0),
            ]);
        }
    }
    
    /**
     * 输出后处理
     */
    public function afterSend($event)
    {
        
    }
    
    /**
     * 将异常转换为array输出
     * @param \Exception $exception
     * @return multitype:string NULL Ambigous <string, \yii\base\string> \yii\web\integer \yii\db\array multitype:string NULL Ambigous <string, \yii\base\string> \yii\web\integer \yii\db\array
     */
    protected function convertExceptionToArray($exception)
    {
        if (!YII_DEBUG && !$exception instanceof UserException && !$exception instanceof HttpException) {
            $exception = new HttpException(500, Yii::t('common', '服务器发生内部错误'));
        }
        
        $array = [
            'name' => ($exception instanceof Exception || $exception instanceof ErrorException) ? $exception->getName() : 'Exception',
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
        ];
        if(empty($array['code']) && ($exception instanceof HttpException)) {
            $array['code'] = $exception->statusCode;
        }
        // 调试模式输出文件行数
        if(YII_DEBUG){
            $array['type'] = get_class($exception);
            if (method_exists($exception, 'getFile') && method_exists($exception, 'getLine')){
                $array['file'] = $exception->getFile();
                $array['line'] = $exception->getLine();
                method_exists($exception, 'getTraceAsString') && ($array['stack-trace'] = explode("\n", $exception->getTraceAsString()));
                if($exception instanceof \yii\db\Exception) {
                    $array['error-info'] = $exception->errorInfo;
                }
            }
        }
        if (($prev = $exception->getPrevious()) !== null) {
            $array['previous'] = $this->convertExceptionToArray($prev);
        }
        return $array;
    }

	// 字母递增
	public static function intToChr($index, $start = 65)
	{
        $str = '';
        if (floor($index / 26) > 0) {
            $str .= self::intToChr(floor($index / 26)-1);
        }
        return $str . chr($index % 26 + $start);
    }
}
