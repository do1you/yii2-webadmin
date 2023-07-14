<?php

/**
 * websocket分布式解决方案
 * 分布式部署时：
 * 启动Register服务：yii daemon/websocket -r
 * 启动Gateway服务：yii daemon/websocket -g --registerHost="Register服务局域网IP" --gatewayLanIp="本机局域网IP" --gatewayPort="对外开放端口"
 * 其他参数：'registerHost'=>'Register服务局域网IP',
 *          'registerPort'=>'Register服务局域网端口',
            'gatewayProtocol'=>'Gateway服务协议', // websocket&http&tcp
            'gatewayPort'=>'Gateway服务端口',
            'gatewayCount'=>'Gateway服务进程数',
            'gatewayLanIp'=>'Gateway服务本机连接IP',
            'gatewayStartPort'=>'Gateway服务起始端口',
            'workerCount'=>'BusinessWorker服务进程数',
 * 启动BusinessWorker服务：yii daemon/websocket -w --registerHost="Register服务局域网IP"
 * 服务端主动推送调用：
 * \GatewayClient\Gateway::$registerAddress = '127.0.0.1:1238';
 * \GatewayClient\Gateway::sendToClient($client_id, $data);
 * 更多方法参考\GatewayClient\Gateway类
 *  Gateway::sendToAll($data);
    Gateway::sendToClient($client_id, $data);
    Gateway::closeClient($client_id);
    Gateway::isOnline($client_id);
    Gateway::bindUid($client_id, $uid);
    Gateway::isUidOnline($uid);
    Gateway::getClientIdByUid($uid);
    Gateway::unbindUid($client_id, $uid);
    Gateway::sendToUid($uid, $dat);
    Gateway::joinGroup($client_id, $group);
    Gateway::sendToGroup($group, $data);
    Gateway::leaveGroup($client_id, $group);
    Gateway::getClientCountByGroup($group);
    Gateway::getClientSessionsByGroup($group);
    Gateway::getAllClientCount();
    Gateway::getAllClientSessions();
    Gateway::setSession($client_id, $session);
    Gateway::updateSession($client_id, $session);
    Gateway::getSession($client_id);
 */
namespace webadmin\modules\daemon\console;

use Yii;
use yii\helpers\Console;
use Workerman\Worker;
use GatewayWorker\Register;
use GatewayWorker\Gateway;
use GatewayWorker\BusinessWorker;

class WebsocketController extends \webadmin\console\CController
{
    /**
     * @var string
     * register服务广播IP
     */
    public $registerHost = '127.0.0.1';
    
    /**
     * @var string
     * register广播端口
     */
    public $registerPort = '1238';
    
    /**
     * @var string
     * gateway监听协议
     */
    public $gatewayProtocol = 'websocket';  // websocket&http&tcp&text
    
    /**
     * @var string
     * gateway监听端口
     */
    public $gatewayPort = '12380';
    
    /**
     * @var int
     * gateway进程数，可以设置为CPU核数数量
     */
    public $gatewayCount = 6;
    
    /**
     * @var string
     * gatewayu部署时本机ip，分布式部署时使用内网ip
     */
    public $gatewayLanIp = '127.0.0.1';
    
    /**
     * @var int
     * gateway内部通讯起始端口，假如gatewayCount=4，起始端口为4000
     * 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
     */
    public $gatewayStartPort = 2900;
    
    /**
     * @var int
     * bussinessWorker进程数量,可以设置为CPU核数2-3倍
     */
    public $workerCount = 12;
    
    /**
     * @var bool
     * 是否分布式部署，将自动获取Gateway服务的本机IP地址，也可以自行指定
     */
    public $service = true;
    
    /**
     * @var bool
     * 是否启动Register服务
     */
    public $register;
    
    /**
     * @var bool
     * 是否启动Gateway服务
     */
    public $gateway;
    
    /**
     * @var bool
     * 是否启动BusinessWorker服务
     */
    public $worker;
    
    /**
     * @var bool
     * 是否以后台服务形式启动服务
     */
    public $daemon;
    
    /**
     * @var int
     * 心跳间隔
     */
    public $pingInterval = 55;
    
    /**
     * @var int
     * pingNotResponseLimit * pingInterval 时间内，客户端未发送任何数据，断开客户端连接
     */
    public $pingNotResponseLimit = 0;
    
    /**
     * @var string
     * 心跳数据
     */
    public $pingData = '{"type":"ping"}';
    
    /**
     * @var Class
     * 事件处理类，正常情况下只需要继承这个类
     */
    public $eventHandler = '\webadmin\modules\daemon\Events';
    
    /**
     * 初始化
     */
    public function init()
    {

    }
    
    /**
     * 不记录日志
     */
    public function afterAction($action, $result)
    {
        if(Worker::getAllWorkers()){
            $runtime = Yii::getAlias('@runtime');
            $unique_prefix = 'process_'.$this->module->id.DIRECTORY_SEPARATOR.\basename(__FILE__,'.php');
            Worker::$pidFile = $runtime.DIRECTORY_SEPARATOR.$unique_prefix.'.pid';
            Worker::$logFile = $runtime.DIRECTORY_SEPARATOR.$unique_prefix.'.workerman.log';
            Worker::$statusFile = $runtime.DIRECTORY_SEPARATOR.$unique_prefix.'.status';
            \yii\helpers\FileHelper::createDirectory(dirname(Worker::$pidFile));
            Worker::runAll();
        }
        
        return parent::afterAction($action, $result);
    }
    
    /**
     * 自定义参数
     */
    public function options($actionID)
    {
        $params = parent::options($actionID);
        $params = array_merge($params,[
            'service','register','gateway','worker','daemon',
            'registerHost','registerPort',
            'gatewayProtocol','gatewayPort','gatewayCount','gatewayLanIp','gatewayStartPort',
            'workerCount','eventHandler',
            'pingInterval','pingData','pingNotResponseLimit',
        ]);
        return $params;
    }
    
    /**
     * 参数别名
     */
    public function optionAliases()
    {
        $params = parent::optionAliases();
        $params += [
            's' => 'service',
            'r' => 'register',
            'g' => 'gateway',
            'w' => 'worker',
            'd' => 'daemon',
        ];
        return $params;
    }
    
    /**
     * 启动守护进程，默认全部启动，--daemon/-d以服务启动，--register/-r启动Register服务，--gateway/-g启动Gateway服务，--worker/-g启动BusinessWorker服务
     * yii daemon/websocket
     */
    public function actionIndex()
    {
        if($this->register){
            $this->actionRegister();
        }
        if($this->gateway){
            $this->actionGateway();
        }
        if($this->worker){
            $this->actionWorker();
        }
        if(!$this->register && !$this->gateway && !$this->worker){
            $this->actionRegister();
            $this->actionGateway();
            $this->actionWorker();
        }
        return 0;
    }
    
    /**
     * 启动Register服务
     * yii daemon/websocket/register
     */
    public function actionRegister()
    {
        // Register服务注册地址
        $register = new Register("text://0.0.0.0:{$this->registerPort}");
        // Register名称，status方便查看
        $register->name = Yii::$app->name.'Register';
        return 0;
    }
    
    /**
     * 启动Gateway服务
     * yii daemon/websocket/gateway
     */
    public function actionGateway()
    {
        // 采用分布式服务启动的时候，又示设置IP，自动获取局域网IP
        if($this->service && $this->gatewayLanIp=='127.0.0.1'){
            $this->gatewayLanIp = gethostbyname(gethostname());
        }

        // gateway 进程，这里使用Text协议，可以用telnet测试 websocket&http&tcp
        $gateway = new Gateway("{$this->gatewayProtocol}://0.0.0.0:{$this->gatewayPort}");
        // gateway名称，status方便查看
        $gateway->name = Yii::$app->name.'Gateway';
        // gateway进程数
        $gateway->count = $this->gatewayCount;
        // 本机ip，分布式部署时gateway使用的内网连接ip，用于注册到register服务
        $gateway->lanIp = $this->gatewayLanIp;
        // 监听服务采用全网段监听形式
        $gateway->innerTcpWorkerListen = '0.0.0.0'; 
        // 内部通讯起始端口，假如$gateway->count=4，起始端口为4000
        // 则一般会使用4000 4001 4002 4003 4个端口作为内部通讯端口
        $gateway->startPort = $this->gatewayStartPort;
        // 服务注册地址
        $gateway->registerAddress = "{$this->registerHost}:{$this->registerPort}";
        
        // 心跳间隔
        $gateway->pingInterval = $this->pingInterval;
        // 心跳数据
        $gateway->pingData = $this->pingData;
        // $pingNotResponseLimit * $pingInterval 时间内，客户端未发送任何数据，断开客户端连接
        $gateway->pingNotResponseLimit = $this->pingNotResponseLimit;
        
        // 当客户端连接上来时，设置连接的onWebSocketConnect，即在websocket握手时的回调
        /*$gateway->onConnect = function($connection){
            $connection->onWebSocketConnect = function($connection , $http_header){
                // 可以在这里判断连接来源是否合法，不合法就关掉连接
                // $_SERVER['HTTP_ORIGIN']标识来自哪个站点的页面发起的websocket链接
                if($_SERVER['HTTP_ORIGIN'] != 'http://kedou.workerman.net'){
                    $connection->close();
                }
                // onWebSocketConnect 里面$_GET $_SERVER是可用的
                // var_dump($_GET, $_SERVER);
            };
        };*/
        return 0;
    }
    
    /**
     * 启动BusinessWorker服务
     * yii daemon/websocket/worker
     */
    public function actionWorker()
    {
        // bussinessWorker 进程
        $worker = new BusinessWorker();
        // worker名称
        $worker->name = Yii::$app->name.'Worker';
        // bussinessWorker进程数量
        $worker->count = $this->workerCount;
        // 服务注册地址
        $worker->registerAddress = "{$this->registerHost}:{$this->registerPort}";
        // 事件处理类
        $worker->eventHandler = $this->eventHandler;
        return 0;
    }
    
    /**
     * 向指定用户端发送信息
     * yii daemon/websocket/send
     */
    public function actionSend($client_id, $data)
    {
        if($client_id && $data){
            \GatewayWorker\Lib\Gateway::$registerAddress = "{$this->registerHost}:{$this->registerPort}";
            \GatewayWorker\Lib\Gateway::sendToClient($client_id, $data);
        }
        
        return 0;
    }
    
    /**
     * 向所有用户或批量用户发送信息
     * yii daemon/websocket/sendall
     */
    public function actionSendall($data, $client_id_array = null, $exclude_client_id = null)
    {
        if($data){
            if($client_id_array && is_array($client_id_array)){
                $client_id_array = implode(",",$client_id_array);
            }
            if($exclude_client_id && is_array($exclude_client_id)){
                $exclude_client_id = implode(",",$exclude_client_id);
            }
            \GatewayWorker\Lib\Gateway::$registerAddress = "{$this->registerHost}:{$this->registerPort}";
            \GatewayWorker\Lib\Gateway::sendToAll($data, $client_id_array, $exclude_client_id);
        }
        
        return 0;
    }
}
