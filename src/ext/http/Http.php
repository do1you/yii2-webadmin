<?php

/**
 * HTTP功能工厂方法类
 *
 * 调用示例代码：
  try {
  $http = Http::factory('http://www.baidu.com', Http::TYPE_SOCK );
  echo $http->get();
  $http = Http::factory('http://127.0.0.1/test/i.php', Http::TYPE_SOCK );
  echo $http->post('', array('user'=>'我们', 'nick'=>'ASSADF@#!32812989+-239%ASDF'), '', array('aa'=>'bb', 'cc'=>'dd'));
  } catch (Exception $e) {
  echo $e->getMessage();
  }
 */
class Http {
    /**
     * @var 使用 CURL
     */
    const TYPE_CURL = 1;
    /**
     * @var 使用 Socket
     */
    const TYPE_SOCK = 2;
    /**
     * @var 使用 Stream
     */
    const TYPE_STREAM = 3;

    /**
     * 保证对象不被clone
     */
    private function __clone() {

    }

    /**
     * 构造函数
     */
    private function __construct() {

    }

    /**
     * HTTP工厂操作方法
     *
     * @param string $url 需要访问的URL
     * @param int $type 需要使用的HTTP类
     * @return object
     */
    public static function factory($url = '', $type = self::TYPE_SOCK) {
        if ($type == '') {
            $type = self::TYPE_SOCK;
        }
        switch ($type) {
            case self::TYPE_CURL :
                if (!function_exists('curl_init')) {
                    throw new Exception(__CLASS__ . " PHP CURL extension not install");
                }
                $obj = Http_Curl::getInstance($url);
                break;
            case self::TYPE_SOCK :
                if (!function_exists('fsockopen')) {
                    throw new Exception(__CLASS__ . " PHP function fsockopen() not support");
                }
                $obj = Http_Sock::getInstance($url);
                break;
            case self::TYPE_STREAM :
                if (!function_exists('stream_context_create')) {
                    throw new Exception(__CLASS__ . " PHP Stream extension not install");
                }
                $obj = Http_Stream::getInstance($url);
                break;
            default:
                throw new Exception("http access type $type not support");
        }
        $obj->reset();
        return $obj;
    }

    /**
     * 生成一个供Cookie或HTTP GET Query的字符串
     *
     * @param array $data 需要生产的数据数组，必须是 Name => Value 结构
     * @param string $sep 两个变量值之间分割的字符，缺省是 &
     * @return string 返回生成好的Cookie查询字符串
     */
    public static function makeQuery($data, $sep = '&') {
        $encoded = '';
        while (list($k, $v) = each($data)) {
            $encoded .= ( $encoded ? "$sep" : "");
            $encoded .= rawurlencode($k) . "=" . rawurlencode($v);
        }
        return $encoded;
    }

}

/**
 * 使用CURL 作为核心操作的HTTP访问类
 *
 * @desc CURL 以稳定、高效、移植性强作为很重要的HTTP协议访问客户端，必须在PHP中安装 CURL 扩展才能使用本功能
 */
class Http_Curl {

    /**
     * @var object 对象单例
     */
    static $_instance = NULL;
    /**
     * @var string 需要发送的cookie信息
     */
    private $cookies = '';
    /**
     * @var array 需要发送的头信息
     */
    private $header = array();
    /**
     * @var string 需要访问的URL地址
     */
    private $uri = '';
    /**
     * @var array 需要发送的数据
     */
    private $vars = array();
    /**
     * @var int 递归请求最大数量
     */
    public $maxNum = 10; 
    /**
     * @var int 递归请求数量
     */
    private $currNum = 0;
    /**
     * @var array 递归请求参数
     */
    private $recursive = array();

    /**
     * 构造函数
     *
     * @param string $configFile 配置文件路径
     */
    private function __construct($url) {
        $this->uri = $url;
    }

    /**
     * 保证对象不被clone
     */
    private function __clone() {

    }
    
    /**
     * 重置参数，主要用于第二次重新请求
     */
    public function reset() {
        $this->uri = $this->cookies = '';
        $this->header = $this->vars = $this->recursive = array();
        $this->maxNum = 10;
        $this->currNum = 0;
    }

    /**
     * 获取对象唯一实例
     *
     * @param string $configFile 配置文件路径
     * @return object 返回本对象实例
     */
    public static function getInstance($url = '') {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($url);
        }
        return self::$_instance;
    }

    /**
     * 设置需要发送的HTTP头信息
     *
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *       或单一的一条类似于 'Host: example.com' 头信息字符串
     * @return void
     */
    public function setHeader($header) {
        if (empty($header)) {
            return;
        }
        if (is_array($header)) {
            foreach ($header as $k => $v) {
                $this->header[] = is_numeric($k) ? trim($v) : (trim($k) . ": " . trim($v));
            }
        } elseif (is_string($header)) {
            $this->header[] = $header;
        }
    }

    /**
     * 设置Cookie头信息
     *
     * 注意：本函数只能调用一次，下次调用会覆盖上一次的设置
     *
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @return void
     */
    public function setCookie($cookie) {
        if (empty($cookie)) {
            return;
        }
        if (is_array($cookie)) {
            $this->cookies = Http::makeQuery($cookie, ';');
        } elseif (is_string($cookie)) {
            $this->cookies = $cookie;
        }
    }

    /**
     * 设置要发送的数据信息
     *
     * 注意：本函数只能调用一次，下次调用会覆盖上一次的设置
     *
     * @param array 设置需要发送的数据信息，一个类似于 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @return void
     */
    public function setVar($vars) {
        if (empty($vars)) {
            return;
        }
        $this->vars = $vars;
    }

    /**
     * 设置要请求的URL地址
     *
     * @param string $url 需要设置的URL地址
     * @return void
     */
    public function setUrl($url) {
        if ($url != '') {
            $this->uri = $url;
        }
    }

    /**
     * 发送HTTP GET请求
     *
     * @param string $url 如果初始化对象的时候没有设置或者要设置不同的访问URL，可以传本参数
     * @param array $vars 需要单独返送的GET变量
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *         或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function get($url = '', $vars = array(), $header = array(), $cookie = '', $timeout = 15, $options = array()) {
        $this->setUrl($url);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('GET', $timeout, $options);
    }

    /**
     * 发送HTTP POST请求
     *
     * @param string $url 如果初始化对象的时候没有设置或者要设置不同的访问URL，可以传本参数
     * @param array $vars 需要单独返送的GET变量
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *         或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function post($url = '', $vars = array(), $header = array(), $cookie = '', $timeout = 15, $options = array()) {
        $this->setUrl($url);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('POST', $timeout, $options);
    }

    /**
     * 发送HTTP请求核心函数
     *
     * @param string $method 使用GET还是POST方式访问
     * @param array $vars 需要另外附加发送的GET/POST数据
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return string 返回服务器端读取的返回数据
     */
    public function send($method = 'GET', $timeout = 15, $options = array()) {
        //处理参数是否为空
        if ($this->uri == '') {
            throw new Exception(__CLASS__ . ": Access url is empty");
        }
        
        // 判断跳转是否上限
        if($this->currNum>$this->maxNum){
        	throw new Exception(__CLASS__ . ": Maximum (".$this->maxNum.") redirects followed");
        }

        //初始化CURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		
		//设置HTTP缺省头
		if (empty($this->header)) {
		    $this->header = array(
		        'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; InfoPath.1)',
		        'Content-type: application/x-www-form-urlencoded',
		        'Expect:',
		        //'Accept-Language: zh-cn',
		        //'Cache-Control: no-cache',
		    );
		}

        //设置特殊属性
        if (!empty($options)) {
            curl_setopt_array($ch, $options);
        }
        //处理GET请求参数
        if ($method == 'GET' && !empty($this->vars)) {
            $query = Http::makeQuery($this->vars);
            $parse = parse_url($this->uri);
            $sep = isset($parse['query']) ? '&' : '?';
            $this->uri .= $sep . $query;
        }
        //处理POST请求数据
        if ($method == 'POST' && !empty($this->vars)) {
            curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, (is_array($this->vars) ? Http::makeQuery($this->vars) : $this->vars));
        }
        
        // 解析网址
        $url = parse_url($this->uri);
        $host = $url['host'];
        $port = isset($url['port']) && ($url['port'] != '') ? $url['port'] : 80;

        //设置cookie信息
        if (!empty($this->cookies) || !empty($this->recursive[$host][$port])) {
        	if(!empty($this->recursive[$host][$port])){
        		$this->cookies .= Http::makeQuery($this->recursive[$host][$port],';');
        	}
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookies);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->header);
        curl_setopt($ch, CURLOPT_URL, $this->uri);

		// 增加 HTTP Header（头）里的字段 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        
        //发送请求读取输数据
        $data = curl_exec($ch);
        if (($err = curl_error($ch))) {
            curl_close($ch);
            throw new Exception(__CLASS__ . " error: " . $err);
        }else{
        	$this->currNum++;
        }
        
		//处理301,302跳转页面访问
        $lines = explode("\n",$data);
        $first = preg_split("/\s/", trim($lines[0]));
        if (isset($first[1]) && in_array($first[1], array('301', '302'))) {
        	foreach ($lines as $line) {
                if($line == "\n" || $line == "\r\n"){break;}
                $second = preg_split("/\s+/", trim($line));
                if (ucfirst(trim($second[0])) == 'Location:' && $second[1] != '') { // 跳转地址
                    $this->header = array();
                    $reurl = trim($second[1]);
                }
                if(ucfirst(trim($second[0])) == 'Set-Cookie:' && $second[1] != ''){ // 跳转记录回归请求的cookie
                	unset($second[0]);
                	$cacheCookies = explode(';',implode(' ',$second));
                	foreach($cacheCookies as $cvalue){
                		list($k,$v) = explode('=',trim($cvalue));
                		if($k && !in_array(strtolower(trim($k)),array('httponly','path','expires','secure'))){
                			$this->recursive[$host][$port][trim($k)] = trim($v);
                		}
                	}
                }
            }
        }else{
        	foreach ($lines as $l=>$line){
        		if($line === "" || ord($line) == 13){
        			unset($lines[$l]);
        			$data = implode("\n",$lines);
        			break;
        		}else{
        			unset($lines[$l]);
        		}
        	}
        }
        if(!empty($reurl)){
			curl_close($ch);
        	return $this->get($reurl);
        } 
        
        curl_close($ch);

        return $data;
    }

}

/**
 * 使用 Socket操作(fsockopen) 作为核心操作的HTTP访问接口
 *
 * @desc Network/fsockopen 是PHP内置的一个Sokcet网络访问接口，必须安装/打开 fsockopen 函数本类才能工作，
 *    同时确保其他相关网络环境和配置是正确的
 */
class Http_Sock {

    /**
     * @var object 对象单例
     */
    static $_instance = NULL;
    /**
     * @var string 需要发送的cookie信息
     */
    private $cookies = '';
    /**
     * @var array 需要发送的头信息
     */
    private $header = array();
    /**
     * @var string 需要访问的URL地址
     */
    private $uri = '';
    /**
     * @var array 需要发送的数据
     */
    private $vars = array();
    /**
     * @var int 递归请求最大数量
     */
    public $maxNum = 10; 
    /**
     * @var int 递归请求数量
     */
    private $currNum = 0;
    /**
     * @var array 递归请求参数
     */
    private $recursive = array();

    /**
     * 构造函数
     *
     * @param string $configFile 配置文件路径
     */
    private function __construct($url) {
        $this->uri = $url;
    }

    /**
     * 保证对象不被clone
     */
    private function __clone() {

    }
    
    /**
     * 重置参数，主要用于第二次重新请求
     */
    public function reset() {
        $this->uri = $this->cookies = '';
        $this->header = $this->vars = $this->recursive = array();
        $this->maxNum = 10;
        $this->currNum = 0;
    }

    /**
     * 获取对象唯一实例
     *
     * @param string $configFile 配置文件路径
     * @return object 返回本对象实例
     */
    public static function getInstance($url = '') {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($url);
        }
        return self::$_instance;
    }

    /**
     * 设置需要发送的HTTP头信息
     *
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *       或单一的一条类似于 'Host: example.com' 头信息字符串
     * @return void
     */
    public function setHeader($header) {
        if (empty($header)) {
            return;
        }
        if (is_array($header)) {
            foreach ($header as $k => $v) {
                $this->header[] = is_numeric($k) ? trim($v) : (trim($k) . ": " . trim($v));
            }
        } elseif (is_string($header)) {
            $this->header[] = $header;
        }
    }

    /**
     * 设置Cookie头信息
     *
     * 注意：本函数只能调用一次，下次调用会覆盖上一次的设置
     *
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @return void
     */
    public function setCookie($cookie) {
        if (empty($cookie)) {
            return;
        }
        if (is_array($cookie)) {
            $this->cookies = Http::makeQuery($cookie, ';');
        } elseif (is_string($cookie)) {
            $this->cookies = $cookie;
        }
    }

    /**
     * 设置要发送的数据信息
     *
     * 注意：本函数只能调用一次，下次调用会覆盖上一次的设置
     *
     * @param array 设置需要发送的数据信息，一个类似于 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @return void
     */
    public function setVar($vars) {
        if (empty($vars)) {
            return;
        }
        $this->vars = $vars;
    }

    /**
     * 设置要请求的URL地址
     *
     * @param string $url 需要设置的URL地址
     * @return void
     */
    public function setUrl($url) {
        if ($url != '') {
            $this->uri = $url;
        }
    }

    /**
     * 发送HTTP GET请求
     *
     * @param string $url 如果初始化对象的时候没有设置或者要设置不同的访问URL，可以传本参数
     * @param array $vars 需要单独返送的GET变量
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *         或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function get($url = '', $vars = array(), $header = array(), $cookie = '', $timeout = 15, $options = array()) {
        $this->setUrl($url);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('GET', $timeout, $options);
    }

    /**
     * 发送HTTP POST请求
     *
     * @param string $url 如果初始化对象的时候没有设置或者要设置不同的访问URL，可以传本参数
     * @param array $vars 需要单独返送的GET变量
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *         或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function post($url = '', $vars = array(), $header = array(), $cookie = '', $timeout = 15, $options = array()) {
        $this->setUrl($url);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('POST', $timeout, $options);
    }

    /**
     * 发送HTTP请求核心函数
     *
     * @param string $method 使用GET还是POST方式访问
     * @param array $vars 需要另外附加发送的GET/POST数据
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return string 返回服务器端读取的返回数据
     */
    public function send($method = 'GET', $timeout = 15, $options = array()) {
        //处理参数是否为空
        if ($this->uri == '') {
            throw new Exception(__CLASS__ . ": Access url is empty");
        }
        
        // 判断跳转是否上限
        if($this->currNum>$this->maxNum){
        	throw new Exception(__CLASS__ . ": Maximum (".$this->maxNum.") redirects followed");
        }
        
        //设置HTTP缺省头
        if (empty($this->header)) {
            $this->header = array(
                'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; InfoPath.1)',
                'Content-type: application/x-www-form-urlencoded',
                'Expect:',
                //'Accept-Language: zh-cn',
                //'Cache-Control: no-cache',
            );
        }

        //处理GET请求参数
        if ($method == 'GET' && !empty($this->vars)) {
            $query = Http::makeQuery($this->vars);
            $parse = parse_url($this->uri);
            $sep = isset($parse['query']) && ($parse['query'] != '') ? '&' : '?';
            $this->uri .= $sep . $query;
        }

        //处理POST请求数据
        $data = '';
        if ($method == 'POST' && !empty($this->vars)) {
            $data = is_array($this->vars) ? Http::makeQuery($this->vars) : $this->vars;
            $this->setHeader('Content-Length: ' . strlen($data));
        }

        //解析URL地址
        $url = parse_url($this->uri);
        $host = $url['host'];
        $port = isset($url['port']) && ($url['port'] != '') ? $url['port'] : 80;
        $path = isset($url['path']) && ($url['path'] != '') ? $url['path'] : '/';
        $path .= isset($url['query']) ? "?" . $url['query'] : '';

        //组织HTTP请求头信息
        array_unshift($this->header, $method . " " . $path . " HTTP/1.1");
        //if (!preg_match("/^[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}$/", $host)) {
            $this->setHeader("Host: " . $host.($port==80 ? '' : ':'.$port));
        //}
        if ($this->cookies != '' || !empty($this->recursive[$host][$port])) {
        	if(!empty($this->recursive[$host][$port])){
        		$this->cookies .= Http::makeQuery($this->recursive[$host][$port],';');
        	}
            $this->setHeader("Cookie: " . $this->cookies);
        }
        $this->setHeader("Connection: Close");

        //构造请求信息
        $header = '';
        foreach ($this->header as $h) {
            $header .= $h . "\r\n";
        }
        $header .= "\r\n";
        if ($method == 'POST' && $data != '') {
            $header .= $data . "\r\n";
        }

        //连接服务器发送请求数据
        $ip = gethostbyname($host);
        if (!($fp = fsockopen($ip, $port, $errno, $errstr, $timeout))) {
            throw new Exception(__CLASS__ . ": Can't connect $host:$port, errno:$errno,message:$errstr");
        }else{
        	$this->currNum++;
        }        
        fputs($fp, $header);
        $lineSize = 1024;

        //处理301,302跳转页面访问
        $line = fgets($fp, $lineSize);
        $first = preg_split("/\s/", trim($line));
        if (isset($first[1]) && in_array($first[1], array('301', '302'))) {
            while (!feof($fp)) {
                $line = fgets($fp, $lineSize);
                if($line == "\n" || $line == "\r\n"){break;}
                $second = preg_split("/\s+/", trim($line));
                if (ucfirst(trim($second[0])) == 'Location:' && $second[1] != '') { // 跳转地址
                    $this->header = array();
                    $reurl = trim($second[1]);
                }
                if(ucfirst(trim($second[0])) == 'Set-Cookie:' && $second[1] != ''){ // 跳转记录回归请求的cookie
                	unset($second[0]);
                	$cacheCookies = explode(';',implode(' ',$second));
                	foreach($cacheCookies as $cvalue){
                		list($k,$v) = explode('=',trim($cvalue));
                		if($k && !in_array(strtolower(trim($k)),array('httponly','path','expires','secure'))){
                			$this->recursive[$host][$port][trim($k)] = trim($v);
                		}
                	}
                }
            }
        }
        if(!empty($reurl)){
        	return $this->get($reurl);
        }      

        //正常读取返回数据
        $buf = '';
        $inheader = 1;
        $chunked_len = 0;
        $chunked_format = 0;
        while (!feof($fp)) {
        	if($inheader){
        		if ($line == "\n" || $line == "\r\n") {
	                $inheader = 0;
	            }else{
	            	list($hname,$hvalue) = explode(':',$line);
	            	if(trim(strtolower($hvalue))=='chunked'){
	            		$chunked_format = 1;
	            	}
	            }
        	}
            
            $line = fgets($fp, $lineSize);
            if($inheader == 0){
            	if($chunked_format){ // 处理chunked数据
            		if ($chunked_len<=0){
		              $chunked_len=hexdec($line);
		              if ($chunked_len==0) break;
		              else continue;
		            } else {
		              $chunked_len-=strlen($line);
		              if ($chunked_len<=0) $line=trim($line);
		            }
            	}
            	$buf .=$line;
            }
        }
        fclose($fp);

        return $buf;
    }

}

/**
 * 使用文件流操作函数为核心操作的HTTP访问接口
 *
 * @desc stream_* 和 fopen/file_get_contents 是PHP内置的一个流和文件操作接口，必须打开 fsockopen 函数本类才能工作，
 *    同时确保其他相关网络环境和配置是正确的，包括 allow_url_fopen 等设置
 */
class Http_Stream {

    /**
     * @var object 对象单例
     */
    static $_instance = NULL;
    /**
     * @var string 需要发送的cookie信息
     */
    private $cookies = '';
    /**
     * @var array 需要发送的头信息
     */
    private $header = array();
    /**
     * @var string 需要访问的URL地址
     */
    private $uri = '';
    /**
     * @var array 需要发送的数据
     */
    private $vars = array();
    /**
     * @var int 递归请求最大数量
     */
    public $maxNum = 10; 
    /**
     * @var int 递归请求数量
     */
    private $currNum = 0;
    /**
     * @var array 递归请求参数
     */
    private $recursive = array();

    /**
     * 构造函数
     *
     * @param string $configFile 配置文件路径
     */
    private function __construct($url) {
        $this->uri = $url;
    }

    /**
     * 保证对象不被clone
     */
    private function __clone() {

    }
    
    /**
     * 重置参数，主要用于第二次重新请求
     */
    public function reset() {
        $this->uri = $this->cookies = '';
        $this->header = $this->vars = $this->recursive = array();
        $this->maxNum = 10;
        $this->currNum = 0;
    }

    /**
     * 获取对象唯一实例
     *
     * @param string $configFile 配置文件路径
     * @return object 返回本对象实例
     */
    public static function getInstance($url = '') {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self($url);
        }
        return self::$_instance;
    }

    /**
     * 设置需要发送的HTTP头信息
     *
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *       或单一的一条类似于 'Host: example.com' 头信息字符串
     * @return void
     */
    public function setHeader($header) {
        if (empty($header)) {
            return;
        }
        if (is_array($header)) {
            foreach ($header as $k => $v) {
                $this->header[] = is_numeric($k) ? trim($v) : (trim($k) . ": " . trim($v));
            }
        } elseif (is_string($header)) {
            $this->header[] = $header;
        }
    }

    /**
     * 设置Cookie头信息
     *
     * 注意：本函数只能调用一次，下次调用会覆盖上一次的设置
     *
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @return void
     */
    public function setCookie($cookie) {
        if (empty($cookie)) {
            return;
        }
        if (is_array($cookie)) {
            $this->cookies = Http::makeQuery($cookie, ';');
        } elseif (is_string($cookie)) {
            $this->cookies = $cookie;
        }
    }

    /**
     * 设置要发送的数据信息
     *
     * 注意：本函数只能调用一次，下次调用会覆盖上一次的设置
     *
     * @param array 设置需要发送的数据信息，一个类似于 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @return void
     */
    public function setVar($vars) {
        if (empty($vars)) {
            return;
        }
        $this->vars = $vars;
    }

    /**
     * 设置要请求的URL地址
     *
     * @param string $url 需要设置的URL地址
     * @return void
     */
    public function setUrl($url) {
        if ($url != '') {
            $this->uri = $url;
        }
    }

    /**
     * 发送HTTP GET请求
     *
     * @param string $url 如果初始化对象的时候没有设置或者要设置不同的访问URL，可以传本参数
     * @param array $vars 需要单独返送的GET变量
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *         或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function get($url = '', $vars = array(), $header = array(), $cookie = '', $timeout = 15, $options = array()) {
        $this->setUrl($url);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('GET', $timeout, $options);
    }

    /**
     * 发送HTTP POST请求
     *
     * @param string $url 如果初始化对象的时候没有设置或者要设置不同的访问URL，可以传本参数
     * @param array $vars 需要单独返送的GET变量
     * @param array/string 需要设置的头信息，可以是一个 类似 array('Host: example.com', 'Accept-Language: zh-cn') 的头信息数组
     *         或单一的一条类似于 'Host: example.com' 头信息字符串
     * @param string/array 需要设置的Cookie信息，一个类似于 'name1=value1&name2=value2' 的Cookie字符串信息，
     *         或者是一个 array('name1'=>'value1', 'name2'=>'value2') 的一维数组
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return unknown
     */
    public function post($url = '', $vars = array(), $header = array(), $cookie = '', $timeout = 15, $options = array()) {
        $this->setUrl($url);
        $this->setHeader($header);
        $this->setCookie($cookie);
        $this->setVar($vars);
        return $this->send('POST', $timeout, $options);
    }

    /**
     * 发送HTTP请求核心函数
     *
     * @param string $method 使用GET还是POST方式访问
     * @param array $vars 需要另外附加发送的GET/POST数据
     * @param int $timeout 连接对方服务器访问超时时间，单位为秒
     * @param array $options 当前操作类一些特殊的属性设置
     * @return string 返回服务器端读取的返回数据
     */
    public function send($method = 'GET', $timeout = 15, $options = array()) {
        //处理参数是否为空
        if ($this->uri == '') {
            throw new Exception(__CLASS__ . ": Access url is empty");
        }
        
        // 判断跳转是否上限
        if($this->currNum>$this->maxNum){
        	throw new Exception(__CLASS__ . ": Maximum (".$this->maxNum.") redirects followed");
        }
        
        $parse = parse_url($this->uri);
        $host = $parse['host'];
        $port = isset($parse['port']) && ($parse['port'] != '') ? $parse['port'] : 80;
        
        //设置HTTP缺省头
        if (empty($this->header)) {
            $this->header = array(
                'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; InfoPath.1)',
                'Content-type: application/x-www-form-urlencoded',
                'Expect:',
                //'Accept-Language: zh-cn',
                //'Cache-Control: no-cache',
            );
        }

        //处理GET请求参数
        if ($method == 'GET' && !empty($this->vars)) {
            $query = Http::makeQuery($this->vars);
            $sep = isset($parse['query']) && ($parse['query'] != '') ? '&' : '?';
            $this->uri .= $sep . $query;
        }

        //处理POST请求数据
        $data = '';
        if ($method == 'POST' && !empty($this->vars)) {
            $data = is_array($this->vars) ? $this->vars : Http::makeQuery($this->vars);
        }

        //设置缺省头
        //if (!preg_match("/^[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}$/", $host)) {
            $this->setHeader("Host: " . $host.($port==80 ? '' : ':'.$port));
        //}
        if ($this->cookies != '' || !empty($this->recursive[$host][$port])) {
        	if(!empty($this->recursive[$host][$port])){
        		$this->cookies .= Http::makeQuery($this->recursive[$host][$port],';');
        	}
            $this->setHeader("Cookie: " . $this->cookies);
        }
        $this->setHeader("Connection: Close");
        //'Accept-Language: zh-cn',
        //'Cache-Control: no-cache',
        //构造头信息
        $opts = array(
            'http' => array(
                'method' => $method,
                'timeout' => $timeout,
            )
        );
        if ($data != '') {
            $opts['http']['content'] = $data;
        }
        $opts['http']['header'] = '';
        $opts['http']['max_redirects']=1; // 允许最多跳转次数
        foreach ($this->header as $h) {
            $opts['http']['header'] .= $h . "\r\n";
        }
        
        //读取扩展设置选项
        if (!empty($options)) {
            isset($options['proxy']) ? $opts['http']['proxy'] = $options['proxy'] : '';
            isset($options['max_redirects']) ? $opts['http']['max_redirects'] = $options['max_redirects'] : '';
            isset($options['request_fulluri']) ? $opts['http']['request_fulluri'] = $options['request_fulluri'] : '';
        }
		//print_r($opts);exit;
        //发送数据返回
        $context = stream_context_create($opts);
        if (($buf = @file_get_contents($this->uri, null, $context)) === false) {
			if(!empty($http_response_header)){
				$this->currNum++;
				$first = preg_split("/\s/", trim($http_response_header[0]));
		        if (isset($first[1]) && in_array($first[1], array('301', '302'))) {
		            foreach ($http_response_header as $line) {
		                $second = preg_split("/\s+/", trim($line));
		                if (ucfirst(trim($second[0])) == 'Location:' && $second[1] != '') { // 跳转地址
		                    $this->header = array();
		                    $reurl = trim($second[1]);
		                }
		                if(ucfirst(trim($second[0])) == 'Set-Cookie:' && $second[1] != ''){ // 跳转记录回归请求的cookie
		                	unset($second[0]);
		                	$cacheCookies = explode(';',implode(' ',$second));
		                	foreach($cacheCookies as $cvalue){
		                		list($k,$v) = explode('=',trim($cvalue));
		                		if($k && !in_array(strtolower(trim($k)),array('httponly','path','expires','secure'))){
		                			$this->recursive[$host][$port][trim($k)] = trim($v);
		                		}
		                	}
		                }
		            }
		        }
		        if(!empty($reurl)){
		        	return $this->get($reurl);
		        }
			}
            throw new Exception(__CLASS__ . ": file_get_contents(" . $this->uri . ") fail");
        }else{
        	$this->currNum++;
        }

        return $buf;
    }

}

