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
    
    /**
     * 格式化数据用
     */
    public $serializer = '\webadmin\restful\Serializer';
    
    // 初始化
    public function init()
    {
        parent::init();
        
        // 初始化模块
        \webadmin\modules\config\models\SysModules::initModule();
        
        // 定义用户
        if(!Yii::$app->has('user')){
            Yii::$app->setComponents([
                'user' => [
                    'class' => '\yii\web\User',
                    'identityClass' => '\webadmin\modules\authority\models\AuthUser',
                    'enableAutoLogin' => true,
                    'enableSession' => true,
                    'loginUrl'=>['/authority/user/login'],
                ]
            ]);
        }
        
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
        Yii::setAlias('@assetPath', $assetPath);
        Yii::setAlias('@assetUrl', $assetUrl);
        
        // AJAX请求页面忽略布局文件
        Yii::$app->request->isAjax && ($this->layout = 'console_ajax');
        if(!empty($_REQUEST['layout'])) $this->layout = trim($_REQUEST['layout']);
        $this->layout = $this->layout ? '@webadmin/views/'.$this->layout : false;
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
                'class' => \webadmin\behaviors\WebAuthFilter::className(),
                'isAccessToken' => $this->isAccessToken,
            ],
            // 缓存查询条件
            'searchBehaviors' => [
                'class' => \webadmin\behaviors\SearchBehaviors::className(),
                'searchCacheActions' => $this->searchCacheActions,
            ],
            // 操作日志和数据库日志记录
            'logBehaviors' => [
                'class' => \webadmin\behaviors\LogBehaviors::className(),
            ],
        ];
    }
    
    /**
     * {@inheritdoc}
     */
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        $result = Yii::createObject($this->serializer)->serialize($result);
        if(Yii::$app->getResponse()->format=='html'){
            if(is_array($result)) Yii::$app->getResponse()->format = Yii::$app->getRequest()->isAjax ? 'json' : 'xml';
        }else{
            if(is_string($result)) Yii::$app->getResponse()->format = 'html';
        }
        return $result;
    }
    
    /**
     * 读取excel
     */
    protected function importExecl($file = '', $sheet = 0, $columnCnt = 0, &$options = [])
    {
        return \webadmin\ext\PhpExcel::readfile($file, $sheet, $columnCnt, $options);
    }
    
    /**
     * 导出excel
     */
    protected function export($model, \yii\data\ActiveDataProvider $dataProvider, $titles = [], $filename = null, $options = [])
    {
        return \webadmin\ext\PhpExcel::export($model, $dataProvider, $titles, $filename, $options);
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
    
    
    
}
