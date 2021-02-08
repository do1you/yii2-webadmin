<?php
/**
 * 数据库表 "log_api_request" 的模型对象.
 * @property int $id 流水号
 * @property string $interface 接口名称
 * @property string $url 接口地址
 * @property int $result_code 结果代码
 * @property string $result_msg 结果描述
 * @property string $params 参数
 * @property string $create_time 操作时间
 * @property int $user_id 操作用户
 */

namespace webadmin\modules\logs\models;

use Yii;

class LogApiRequest extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'log_api_request';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['interface', 'url', 'result_code', 'result_msg', 'params', 'create_time'], 'safe'],
            [['user_id'], 'integer'],
            [['params', 'result_msg'], 'string'],
            [['create_time'], 'safe'],
            [['interface'], 'string', 'max' => 80],
            [['url'], 'string', 'max' => 255],
            [['result_code'], 'string', 'max' => 32],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('logs', '流水号'),
            'interface' => Yii::t('logs', '接口名称'),
            'url' => Yii::t('logs', '接口地址'),
            'result_code' => Yii::t('logs', '结果代码'),
            'result_msg' => Yii::t('logs', '结果'),
            'params' => Yii::t('logs', '参数'),
            'create_time' => Yii::t('logs', '操作时间'),
            'user_id' => Yii::t('logs', '操作用户'),
        ];
    }
    
    /**
     * 编码转换
     */
    public static function toGbk($vars = '')
    {
        if(is_array($vars)){
            foreach($vars as $k=>$v){
                $vars[$k] = self::toGbk($v);
            }
        }else{
            $vars = iconv('UTF-8', 'GBK//IGNORE', $vars);   // 转换编码
        }
        return $vars;
    }
    
    /**
     * 编码转换
     */
    public static function toUtf8($vars = '')
    {
        if(is_array($vars)){
            foreach($vars as $k=>$v){
                $vars[$k] = self::toUtf8($v);
            }
        }else{
            $vars = iconv('GBK', 'UTF-8//IGNORE', $vars);   // 转换编码
        }
        return $vars;
    }
    
    /**
     * 对外的API请求
     * url：请求地求
     * $vars：请求参数，可以数组，也可以是字符串，body的内容
     * $header：请求头信息，注意默认有缺省值为application/x-www-form-urlencoded
     * $cookie：请求的cookie信息
     * $timeout：请求超时时间
     * $options：其他参数
     * $httpType：请求类型 1 CURL 2 SOCKET 3 Stream
     */
    public static function post($url = '', $vars = [], $header = [], $cookie = '', $timeout = 10, $options = [], $httpType='1', $isGbk=false, $method = 'POST')
    {
        try{
            $data = $isGbk ? self::toGbk($vars) : $vars;   // 转换编码
            if($method=='GET'){
                $result = trim(Yii::createObject('webadmin\ext\http\Httper')->getHttp($httpType)->get($url, $data, $header, $cookie, $timeout, $options));
            }else{
                $result = trim(Yii::createObject('webadmin\ext\http\Httper')->getHttp($httpType)->post($url, $data, $header, $cookie, $timeout, $options));
            }
            $result = $isGbk ? self::toUtf8($result) : $result;   // 转换编码
        }catch(Exception $e) {
            $message = $e->getMessage();
            $code = $e->getCode();
            $result = false;
        }
        
        // 记录接口请求日志
        \webadmin\modules\logs\models\LogApiRequest::insertion([
            'interface' => parse_url($url, PHP_URL_PATH),
            'url' => $url,
            'result_code' => (!empty($code) ? $code : '0'),
            'result_msg' => (!empty($message) ? $message : $result),
            'params' => ($vars ? print_r($vars,true) : ""),
            'create_time' => date('Y-m-d H:i:s'),
            'user_id' => ((Yii::$app instanceof \yii\web\Application && Yii::$app->user->id) ? Yii::$app->user->id : '0'),
        ]);
        
        return $result;
    }
    
    /**
     * GET请求API
     */
    public static function get($url = '', $vars = [], $header = [], $cookie = '', $timeout = 10, $options = [], $httpType='1', $isGbk=false)
    {
        return static::post($url, $vars, $header, $cookie, $timeout, $options, $httpType, $isGbk, 'GET');
    }
    
    // 搜索
    public function search($params,$wheres=[],$with=[], $joinWith=[])
    {
        $this->load($params);
        $wheres = [];
        
        // 操作时间
        if(strlen($this->create_time) && strpos($this->create_time, '至')){
            list($startTime, $endTime) = explode('至',$this->create_time);
            $wheres[] = ['>=','create_time',trim($startTime)];
            $wheres[] = ['<=','create_time',trim($endTime)];
            unset($this->create_time);
        }
        
        // 用户
        if(strlen($this->user_id) && !is_numeric($this->user_id)){
            $joinWith[] = 'user';
            $wheres[] = ['like','name',trim($this->user_id)];
            unset($this->user_id);
        }
        
        $result = parent::search([],$wheres,$with,$joinWith);
        
        $this->load($params);
        
        return $result;
    }
    
    // 获取用户
    public function getUser(){
        return $this->hasOne(\webadmin\modules\authority\models\AuthUser::className(), ['id' => 'user_id']);
    }
}
