<?php
/**
 * HTTP封装类
 *
 * 调用示例代码：Yii::createObject('webadmin\ext\http\Httper')->getHttp('1')->post($url,$data)
 */
namespace webadmin\ext\http;

class Httper{

	public $httpType = 2; // 1 CURL 2 SOCKET 3 Stream
	
	private $_http = array(); // http请求对象
	
	/**
	 * 格式化绝对网址，过滤js,mail等地址
	 * 参数：需要格式化的相对网址，参照的绝对网址，是否提取参照网址同域下的网址，过滤其它协议的网址，失败返回false
	 */
	public function formatUrl($url=null,$furl='',$ishost=false,$filter=true){
		if(is_array($url)){
			foreach($url as $k=>$u){
				$u = $this->formatUrl($u,$furl,$ishost,$filter);
				if($u===false){
					unset($url[$k]);
				}else{
					$url[$k] = $u;
				}
			}
			return $url;
		}
		
	    // 过滤其它协议的网址
	    if($filter || $furl) $urls = parse_url($url);	    
	    if($filter){
	    	if(!empty($urls['scheme']) && !in_array(strtolower($urls['scheme']),array('http','https'))){
	    		return false;
	    	}
	    }
	    
	    // 相对网址变更为绝对网址
	    if($furl){
	    	$furls = parse_url($furl);
	    	if(!empty($furls['scheme']) && !empty($furls['host'])){
	    		// 判断同域
	    		if($ishost 
	    			&& (
	    					(!empty($urls['host']) && $furls['host']!=$urls['host'])
	    					|| ((!empty($furls['port']) || !empty($urls['port'])) && $furls['port']!=$urls['port'])
	    				)
	    		){
		    		return false;
		    	}
		    	// 格式化网址
		    	if(!isset($urls['scheme']) && in_array(strtolower($furls['scheme']),array('http','https'))){
		    		if(substr($url,0,1)=='/'){	    			
			    		$url = $furls['scheme'].'://'.$furls['host'].(isset($furls['port']) ? ':'.$furls['port'] : '').$url;
			    	}else{
			    		$url = trim($url) ? dirname($furl).'/'.$url : dirname($furl);
			    	}
		    	}
	    	}	    	
	    }
	    
	    // 当前目录和上一级目录处理
	    $a=explode("/",$url); 
		$count = count($a);
	    for($z=0;$z<$count;$z++){
	        if($a[$z]==".."){ // 处理上一级路径 
	            unset($a[$z],$a[$z-1]);
	            $url=$this->formatUrl(implode('/',$a));
	            break;
	        }
	        if($a[$z]=="."){ // 处理当前目录路径
	        	unset($a[$z]);
	        	$url=$this->formatUrl(implode('/',$a));
	            break;
	        }
	    }
	    return rtrim(htmlspecialchars_decode($url),'/'); 
	}
	
	// 请求HTTP的方法
	public function __call($name,$parameters)
	{
		$http = $this->getHttp();
		if(method_exists($http,$name)){
			return call_user_func_array(array($http,$name),$parameters);
		}
		
		return parent::__call($name,$parameters);
	}
		
	/**
	 * 返回HTTP请求对象,支持302页面跳转爬取，但不支持JAVASCRIPT的跳转
	 * 示例:
	 * Yii::createObject('webadmin\ext\http\Httper')->get('http://10.1.3.143/idc/portal.php')
	 * Yii::createObject('webadmin\ext\http\Httper')->post('http://10.1.3.143/idc/portal.php',array('a'=>1))
	 */
	public function getHttp($httpType=null){
		$httpType = $httpType===null ? $this->httpType : $httpType;
		if(!isset($this->_http[$httpType])){
			$this->_http[$httpType] = Http::factory('', $httpType);
		}else{
		    $this->_http[$httpType]->reset();
		}
		\webadmin\modules\logs\models\LogApiRequest::$begions[] = microtime(true);
		return $this->_http[$httpType];
	}
}
