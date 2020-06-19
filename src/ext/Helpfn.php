<?php
/**
 * 辅助类方法，从1.0框架迁移过来
 *
 */
namespace webadmin\ext;

use Yii;

class Helpfn
{
	/**
	*  @desc 根据两点间的经纬度计算距离
	*  @param float $lat 纬度值
	*  @param float $lng 经度值
	*/
	public static function getDistance($lat1, $lng1, $lat2 = '', $lng2 = '')
	{
		if($lat2 === '' || $lng2 === '') return 0;
		$earthRadius = 6367000; //approximate radius of earth in meters

		$lat1 = ($lat1 * pi() ) / 180;
		$lng1 = ($lng1 * pi() ) / 180;

		$lat2 = ($lat2 * pi() ) / 180;
		$lng2 = ($lng2 * pi() ) / 180;

		$calcLongitude = $lng2 - $lng1;
		$calcLatitude = $lat2 - $lat1;
		$stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);  
		$stepTwo = 2 * asin(min(1, sqrt($stepOne)));
		$calculatedDistance = $earthRadius * $stepTwo;

		return round($calculatedDistance);
	}

	// 格式化距离
	public static function formatDistance($distance=0)
	{
		if($distance > 999) return $distance/1000.0.'km';
		else return $distance.'m';
	}

	// 保存数据流图片
	public static function savePicture($string=null,$repath='',$extName='jpg')
	{
	    $module = Yii::$app->controller->module ? Yii::$app->controller->module->id : '';
	    if(in_array($module,array())){
	        $storePath = SysConfig::config('store_code').'/'.($repath ? $repath.'/' : '');
	    }else{
	        $storePath = ($repath ? $repath.'/' : '');
	    }
		
		$path = UP_PATH.$storePath.date('Y').'/'.date('m-d').'/';
		$filename = $path.time().'_'.mt_rand(1000,9999).'.'.($extName ? $extName : 'jpg');
		if(!Helpfn::add_dir(ROOT_PATH.$path))
		{
			return false;
		}
		if($string===null) return $filename;
		if( file_put_contents(ROOT_PATH.$filename, $string) )
			return $filename;
		else 
			return false;
	}

	// 创建随机数
	public static function create_order_sn()
	{
		return time() . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
	}

	// emoji内容转义
	public static function encodeEmoji($text='')
	{
		$text = json_encode($text);
		$text = preg_replace_callback("#(\\\u[def][0-9a-f]{3})#i",array('Helpfn','addslashes'),$text); //将emoji的unicode留下，其他不动
		return json_decode($text);
	} 

	// emoji内容反转义
	public static function decodeEmoji($text='')
	{
		$text = json_encode($text);
		$text = preg_replace_callback("#(\\\u[def][0-9a-f]{3})#i",array('Helpfn','stripslashes'),$text); //将emoji的unicode留下，其他不动
		return json_decode($text);
	}

	// emoji内容转义回调
	public static function addslashes($arr=array())
	{
		$text = is_array($arr) ? $arr[1] : $arr;
		return addslashes($text);
	}
	
	// emoji内容反转义回调
	public static function stripslashes($arr=array())
	{
		$text = is_array($arr) ? $arr[1] : $arr;
		return stripslashes($text);
	}

	// 判断是否手机访问
	public static function is_mobile()  
	{  
		$_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';  
		$mobile_browser = '0';  
		if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))  
			$mobile_browser++;  
		if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))  
			$mobile_browser++;  
		if(isset($_SERVER['HTTP_X_WAP_PROFILE']))  
			$mobile_browser++;  
		if(isset($_SERVER['HTTP_PROFILE']))  
			$mobile_browser++;  
		$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));  
		$mobile_agents = array(  
			'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',  
			'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',  
			'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',  
			'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',  
			'newt','noki','oper','palm','pana','pant','phil','play','port','prox',  
			'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',  
			'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',  
			'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',  
			'wapr','webc','winw','winw','xda','xda-'
		);  
		if(in_array($mobile_ua, $mobile_agents))  
			$mobile_browser++;  
		if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)  
			$mobile_browser++;  
		// Pre-final check to reset everything if the user is on Windows  
		if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)  
			$mobile_browser=0;  
		// But WP7 is also Windows, with a slightly different characteristic  
		if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)  
			$mobile_browser++;  
		if($mobile_browser>0)  
			return true;  
		else
			return false;
	}
	
	/*
	 * 极光推送调用参数描述
	 * options：推送参数 sendno：推送序号，time_to_live：离线消息时长，override_msg_id：覆盖的消息ID，apns_production：APNs是否生产环境，big_push_duration：定速推送时长
	 * message：消息内容 msg_content：消息内容，title：消息标题，content_type：消息内容类型，extras：扩展字段
	 * notification：通知内容 alert：通知内容
	 * 				android子集：alert：通知内容，title：通知标题，builder_id：通知栏样式，extras：扩展字段
	 * 				ios子集：alert：通知内容，sound：提示声音，badge：应用角标，content-available：推送唤醒，extras：扩展字段
	 * audience：推送用户设置 all：所有用户，tag：指定标签并集，tag_and：指定标签交集，alias：别名，registration_id：注册ID
	 * platform：推送平台设置  all：全部，android：安卓，ios：IOS，winphone：WIN
	 * app: 1店端 2总部
	 * */
	public static function jpush($title='',$message='',$audience='all',$extras=array(),$message_type='1',$options=array(),$platform='all',$app = 1)
	{
		$data = array();
		$header = array();
		if($app == 1)
			$params = Yii::$app->params['store_jpush'] ? Yii::$app->params['store_jpush'] : array();
		else
			$params = Yii::$app->params['platform_jpush'] ? Yii::$app->params['platform_jpush'] : array();
		$url = 'https://api.jpush.cn/v3/push';
		
		// 验证信息
		$base64=base64_encode("{$params['appKey']}:{$params['masterSecret']}");
		$header[] = 'Content-Type:application/json';
		$header[] = "Authorization:Basic {$base64}";
		
		// 推送平台
		$data['platform'] = $platform;
		
		// 推送用户   --------------局域网 LAN 测试网 LAN_T 正式网 WAN
		$audience = is_array($audience) ? $audience : array();
		$tag_and = !empty($audience['tag_and']) ? $audience['tag_and'] : array();
		if($params['apns_production']=='1'){ // 生产环境
			$tag_and[] = 'WAN';
		}elseif($params['apns_production']=='2'){ // 测试环境
			$tag_and[] = 'LAN_T';
		}else{ // 开发环境
			$tag_and[] = 'LAN';
		}
		$audience['tag_and'] = $tag_and;
		$data['audience'] = $audience;
		
		// 通知内容
		$titleExtras = array_merge(array('title'=>$title),$extras);
		if($message_type=='1'){ // 通知
			$data['notification'] = array(
				"alert"=>$message,
				"android"=>array( // 安卓
					"alert"=>$message,
					"title"=>$title,
					"builder_id"=>1,
					"extras"=> $titleExtras,
				),
				"ios"=>array( // ios
					//"alert"=>$message,
					"sound"=>"default",
					"badge"=>"1",
					"extras"=>$titleExtras,
				),
			);
		}else{
			$data['message'] = array( // 自定义消息
				"title"=> $title,
				"msg_content" =>$message,
				"extras"=>$extras
			);
		}
		
		
		// 推送参数
		$data['options'] = array(
            "sendno" => time(), // 推送序号
            "time_to_live" => '86400', // 离线时间秒数
            "apns_production" => ($params['apns_production']=='1' ? '1' : '0'),        // 指定 APNS环境
        );
        
        //print_r($data);exit;
        $query_string = json_encode($data);
        $result_str = Helpfn::curl_post($url,$query_string,$header);
        // 失败返回结果：{"msg_id": 1815750324, "error": {"message": "cannot find user by this audience", "code": 1011}}
        // 成功返回结果：{"sendno":"18","msg_id":"1828256757"}
		
        $result = $result_str ? json_decode($result_str,true) : false;
        if(is_array($result) && !empty($result['error']['message'])){
        	return $result['error']['message'];
        }elseif(is_array($result) && !empty($result['sendno'])){
        	return true;
        }else{
        	return '推送失败，返回结果'.$result_str;
        }
	}
	
	// 发送 curl原生POST请求
	public static function curl_post($url="",$param="",$header=array())
	{
        if (empty($url) || empty($param)) {
        	return false;
        }

        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$url);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);

        // 增加 HTTP Header（头）里的字段 
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);//运行curl
        
		if (($err = curl_error($ch))) {
            curl_close($ch);
            throw new Exception(__CLASS__ . " error: " . $err);
        }
        
        curl_close($ch);
        return $data;
    }
	
	// XML转数组
	public static function xml_to_array($xml)
	{
		$reg = "/<(\w+)[^>]*>([\\x00-\\xFF]*)<\\/\\1>/";
		if(preg_match_all($reg, $xml, $matches)){
			$count = count($matches[0]);
			for($i = 0; $i < $count; $i++){
			$subxml= $matches[2][$i];
			$key = $matches[1][$i];
				if(preg_match( $reg, $subxml )){
					$arr[$key] = Helpfn::xml_to_array( $subxml );
				}else{
					$arr[$key] = $subxml;
				}
			}
		}
		return $arr;
	}
	
	// 数字转中文
	public static function ParseNumber($number=0)
	{
		$basical = array(0=>"零","一","二","三","四","五","六","七","八","九");
		$advanced = array(1=>"十","百","千");
		$top = array(1=>"万","亿");

		if ($number>999999999999) return "数字太大";
		if ($number==0) return "零";

		for($level=0;$number>0.0001;$level++,$number=floor($number / 10000))
		{
			$n1=substr($number,-1,1);

			if($number>9) $n2=substr($number,-2,1);
			else $n2=0;

			if($number>99) $n3=substr($number,-3,1);
			else $n3=0;

			if($number>999) $n4=substr($number,-4,1);
			else $n4=0;

			if($n4) $parsed[$level].=$basical[$n4].$advanced[3];
			else{
				if(($number/10000)>=1) $parsed[$level].="/零/";
			}
			if($n3) $parsed[$level].=$basical[$n3].$advanced[2];
			else{
				if(!preg_match("/零$/",$parsed[$level]) && ($number / 1000)>=1) $parsed[$level].="零";
			}
			if($n2) $parsed[$level].=$basical[$n2].$advanced[1];
			else{
				if(!preg_match("/零$/",$parsed[$level]) && ($number / 100)>=1) $parsed[$level].="零";
			}
			if($n1) $parsed[$level].=$basical[$n1];
		}

		for($level-=1;$level>=0;$level--)
		{
			$result.=$parsed[$level].$top[$level];
		}

		if(preg_match("/零$/",$result)) $result=substr($result,0,strlen($result)-2);
		return $result;
	}

	// 截取字符串
	public static function sub_str($string, $length, $etc = '...')
	{
		$result = '';
		$string = html_entity_decode(trim(strip_tags($string)), ENT_QUOTES, 'UTF-8');
		$strlen = strlen($string);
		for ($i = 0; (($i < $strlen) && ($length > 0)); $i++){
			if ($number = strpos(str_pad(decbin(ord(substr($string, $i, 1))), 8, '0', STR_PAD_LEFT), '0')){
				if ($length < 1.0){
					break;
				}
				$result .= substr($string, $i, $number);
				$length -= 1.0;
				$i += $number - 1;
			}else{
				$result .= substr($string, $i, 1);
				$length -= 0.5;
			}
		}
		$result = htmlspecialchars($result, ENT_QUOTES, 'UTF-8');
		if ($i < $strlen){
			$result .= $etc;
		}
		return $result;
	}
	
	// 分割中英文汉字
	public static function split_zh($tempaddtext='')
	{  
		$tempaddtext = iconv("UTF-8","GBK//IGNORE", $tempaddtext);  
		$cind = 0;  
		$arr_cont=array();  
	
		for($i=0;$i<strlen($tempaddtext);$i++)  
		{  
			if(strlen(substr($tempaddtext,$cind,1)) > 0){  
				if(ord(substr($tempaddtext,$cind,1)) < 0xA1 ){ //如果为英文则取1个字节  
					array_push($arr_cont,substr($tempaddtext,$cind,1));  
					$cind++;  
				}else{  
					array_push($arr_cont,substr($tempaddtext,$cind,2));  
					$cind+=2;  
				}  
			}  
		}  
		foreach($arr_cont as $k=>$row){  
			$arr_cont[$k]=iconv("GBK","UTF-8//IGNORE",$row);  
		}
	
		return $arr_cont;
	}
	
	// 创建文件夹,支持多级目录
	public static function add_dir($dir='')
	{  
		if(!is_dir($dir))  
		{  
			if(!self::add_dir(dirname($dir)))
			{  
				return false;  
			}  
			if(!file_exists($dir) && !mkdir($dir,0777))
			{  
				return false;  
			}  
		}  
		return true;  
	}
	
	// 删除文件夹
	public static function remove_dir($path='')
	{
	    if($path){
            if(($handle=opendir($path))===false)
                return;
            while(($file=readdir($handle))!==false)
            {
                if($file[0]==='.')
                    continue;
                $fullPath=$path.DIRECTORY_SEPARATOR.$file;
                if(is_dir($fullPath)){
                   self::remove_dir($fullPath);
                }else
                 @unlink($fullPath);
            }
            closedir($handle);
            @rmdir($path);
	    }
	    return;
	}

	// 获取站点目录
	public static function getDocumentRootPath()
	{
        if (isset($_SERVER['SCRIPT_FILENAME'])) {
            $sRealPath = dirname($_SERVER['SCRIPT_FILENAME']);
        }else{
            $sRealPath = realpath( './' ) ;
        }
        
        if(!in_array(substr($sRealPath,-1),array('/','\\'))) $sRealPath.='/';
        $sSelfPath = rtrim(dirname($_SERVER['PHP_SELF']),'/');

        return substr($sRealPath, 0, strlen($sRealPath) - strlen($sSelfPath));
    }
	
	// 格式化总计小数点
	public static function numFormat($arr,$len=100)
	{
	    if(is_array($arr)){
	        foreach($arr as $key=>$val){
	            $arr[$key] = self::numFormat($val,$len);
	        }
	        return $arr;
	    }else{
	        return (is_numeric($arr) ? round($arr*$len)/$len : $arr);
	    }
	}
	
	// 字母递增
	public static function intToChr($index, $start = 65)
	{
        $str = '';
        if (floor($index / 26) > 0) {
            $str .= Helpfn::intToChr(floor($index / 26)-1);
        }
        return $str . chr($index % 26 + $start);
    }
	
    //检证身份证是否正确 
    public static function isCard($card)
    { 
        $card = self::to18Card($card); 
        if (strlen($card) != 18) { 
            return false; 
        } 
  
        $cardBase = substr($card, 0, 17); 
  
        return (self::getVerifyNum($cardBase) == strtoupper(substr($card, 17, 1))); 
    } 
  
  
    //格式化15位身份证号码为18位 
    public static function to18Card($card)
    { 
        $card = trim($card); 
  
        if (strlen($card) == 18) { 
            return $card; 
        } 
  
        if (strlen($card) != 15) { 
            return false; 
        } 
  
        // 如果身份证顺序码是996 997 998 999，这些是为百岁以上老人的特殊编码 
        if (array_search(substr($card, 12, 3), array('996', '997', '998', '999')) !== false) { 
            $card = substr($card, 0, 6) . '18' . substr($card, 6, 9); 
        } else { 
            $card = substr($card, 0, 6) . '19' . substr($card, 6, 9); 
        } 
        $card = $card . self::getVerifyNum($card); 
        return $card; 
    } 
  
    // 计算身份证校验码，根据国家标准gb 11643-1999 
    private static function getVerifyNum($cardBase)
    { 
        if (strlen($cardBase) != 17) { 
            return false; 
        } 
        // 加权因子 
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2); 
  
        // 校验码对应值 
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2'); 
  
        $checksum = 0; 
        for ($i = 0; $i < strlen($cardBase); $i++) { 
            $checksum += substr($cardBase, $i, 1) * $factor[$i]; 
        } 
  
        $mod = $checksum % 11; 
        $verify_number = $verify_number_list[$mod]; 
  
        return $verify_number; 
    } 
    
	// 内容去空格
	public static function array_trim($arr = array())
	{
		if(is_array($arr)){
			foreach($arr as $key=>$val){
				if(is_array($val)){
					$arr[$key] = Helpfn::array_trim($val);
				}else{
					if(!is_object($val)){
						$val = $val&&is_numeric($val) ? floatval(round($val*100)/100) : $val;
						$arr[$key] = trim($val);
					}
				}
			}
		}
		
		return $arr;
	}
	
	// 驼峰处理
	public static function convertUnderline( $str , $ucfirst = true)
	{
	    $str = explode('_' , $str);
	    foreach($str as $key=>$val)
	        $str[$key] = ucfirst($val);
	 
	    if(!$ucfirst)
	        $str[0] = strtolower($str[0]);
	 
	    return implode('' , $str);
	}
	
	//encrypt 加密解密
	public static function encrypt($string,$operation,$key=''){ 
        $key=md5($key); 
        $key_length=strlen($key); 
          $string=$operation=='D'?base64_decode($string):substr(md5($string.$key),0,8).$string; 
        $string_length=strlen($string); 
        $rndkey=$box=array(); 
        $result=''; 
        for($i=0;$i<=255;$i++){ 
               $rndkey[$i]=ord($key[$i%$key_length]); 
            $box[$i]=$i; 
        } 
        for($j=$i=0;$i<256;$i++){ 
            $j=($j+$box[$i]+$rndkey[$i])%256; 
            $tmp=$box[$i]; 
            $box[$i]=$box[$j]; 
            $box[$j]=$tmp; 
        } 
        for($a=$j=$i=0;$i<$string_length;$i++){ 
            $a=($a+1)%256; 
            $j=($j+$box[$a])%256; 
            $tmp=$box[$a]; 
            $box[$a]=$box[$j]; 
            $box[$j]=$tmp; 
            $result.=chr(ord($string[$i])^($box[($box[$a]+$box[$j])%256])); 
        } 
        if($operation=='D'){ 
            if(substr($result,0,8)==substr(md5(substr($result,8).$key),0,8)){ 
                return substr($result,8); 
            }else{ 
                return''; 
            } 
        }else{ 
            return str_replace('=','',base64_encode($result)); 
        } 
    }
    
    //生成随机数
    public static function rand_string($len = 8)
    {
        $len = intval($len);
        if($len <= 0) return '';
        $str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $result = '';
        for($i = 0; $i < $len; $i++){
            $result .= $str[rand(0,35)];
        }
        return $result;
    }

    public static function model_save_error($model,$split='<br>'){
        $r = array();
        foreach( $model->getErrors() as $errors ){
            foreach( $errors as $error ) {
                $r[] = $error;
            }
        }
        return implode($split,$r);
    }
    
}




