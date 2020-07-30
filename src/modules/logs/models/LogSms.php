<?php
/**
 * 数据库表 "log_sms" 的模型对象.
 * @property int $id 流水号
 * @property string $mobile 手机号
 * @property string $content 发送内容
 * @property int $result_code 发送结果
 * @property string $result 结果描述
 * @property string $addtime 时间
 * @property int $user_id 发送用户
 */

namespace webadmin\modules\logs\models;

use Yii;

class LogSms extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'log_sms';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['mobile', 'content', 'result', 'addtime', 'sms_user'], 'safe'],
            [['mobile', 'content'], 'string'],
            [['result_code', 'user_id'], 'integer'],
            [['addtime'], 'safe'],
            [['result'], 'string', 'max' => 100],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('logs', '流水号'),
            'mobile' => Yii::t('logs', '手机号'),
            'content' => Yii::t('logs', '发送内容'),
            'sms_user' => Yii::t('logs', '发送短信账户'),
            'result_code' => Yii::t('logs', '发送结果'),
            'result' => Yii::t('logs', '结果描述'),
            'addtime' => Yii::t('logs', '时间'),
            'user_id' => Yii::t('logs', '发送用户'),
        ];
    }
    
    /**
     * 发送手机短信
     */
    public static function sendSms($mobile, $content='', $codeKeys='')
    {
        if(!$mobile || !$content){
            return Yii::t('logs', '短信发送内容未设置');
        }
        
        // 限制一天内发送五次
        $mobiles = is_array($mobile) ? $mobile : [trim($mobile)];
        $contents = is_array($content) ? $content : [$content];
        $codeKeys = is_array($codeKeys) ? $codeKeys : [$codeKeys];
        if(!defined('YII_DEBUG') || !YII_DEBUG){
            $whiteMobiles = \webadmin\modules\config\models\SysConfig::config('sms_white_mobiles');
            $whiteMobiles = $whiteMobiles ? explode(',', $whiteMobiles) : [];
            foreach($mobiles as $key=>$m){
                if(in_array($m,$whiteMobiles)) continue;
                $cacheKey = 'logSms/mobileCount/'.$m;
                $sum = Yii::app()->cache->get($cacheKey);
                $time = time();
                if($sum && is_array($sum)){
                    foreach($sum as $k=>$v){
                        if($time-$v>(3600*24)){
                            unset($sum[$k]);
                        }
                    }
                    if(count($sum)>=5){
                        unset($mobiles[$key]);
                        if(count($contents) > 1){
                            unset($contents[$key]);
                        }
                        if(count($codeKeys) > 1){
                            unset($codeKeys[$key]);
                        }
                        return sprintf(Yii::t('logs', '手机号码%s在指定时间内发送的次数已达到上限次数'), $m);
                    }
                }else{
                    $sum = [];
                }
                $sum[] = $time;
                Yii::app()->cache->set($cacheKey, $sum, (3600*24));
            }
        }
        
        if(count($mobiles) < 1){
            return Yii::t('logs', '没有允许发送的短信');
        }
        
        // 短信配置
        $url = \webadmin\modules\config\models\SysConfig::config('sms_send_url');
        $api_key = \webadmin\modules\config\models\SysConfig::config('sms_send_username');
        $sign = \webadmin\modules\config\models\SysConfig::config('sms_sign');
        if(empty($url) || empty($api_key)){
            return Yii::t('logs', '未正确配置短信参数');
        }
        
        $apiKeys = [];
        foreach($contents as $key=>$t_content){
            if($sign){
                $contents[$key] = $t_content. "【{$sign}】"; // 增加签名
            }
            $apiKeys[$key] = !empty($codeKeys[$key]) ? $codeKeys[$key] : $api_key;
        }
        
        $data = [];
        $data['app'] = "yz";
        $data['mobile'] = implode(',',$mobiles);
        $data['content'] = json_encode($contents);
        $data['apikey'] = json_encode($apiKeys);
        
        try{
            $result = trim(Yii::createObject('webadmin\ext\http\Httper')->getHttp('1')->post($url,$data));
            $r = json_decode($result, true);
            if(in_array($r["code"],['0','1','3'])) {
                $code = $r["code"];
                if(!empty($r['data']['fail'])){
                    $re = $r;
                }else{
                    $re = true;
                }
            } else {
                $re = $r["msg"];
            }
        }catch(Exception $e) {
            $result = $e->getMessage();
            $re = Yii::t('logs', '短信发送失败，短信通道无响应');
        }
        
        // 记录短信日志
        \webadmin\modules\logs\models\LogSms::insertion([
            'mobile' => $data['mobile'],
            'content' => '"'.implode('","',$contents).'"',
            'sms_user' => trim(trim($data['apikey'],'['),']'),
            'result_code' => isset($code) ? $code : '-1',
            'result' => $result,
            'addtime' => date('Y-m-d H:i:s'),
            'user_id' => ((Yii::$app instanceof \yii\web\Application && Yii::$app->user->id) ? Yii::$app->user->id : '0'),
        ]);

        return $re;
    }
    
    // 搜索
    public function search($params,$wheres=[],$with=[], $joinWith=[])
    {
        $this->load($params);
        $wheres = [];
        
        // 操作时间
        if(strlen($this->addtime) && strpos($this->addtime, '至')){
            list($startTime, $endTime) = explode('至',$this->addtime);
            $wheres[] = ['>=','addtime',trim($startTime)];
            $wheres[] = ['<=','addtime',trim($endTime)];
            unset($this->addtime);
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
    
    // 获取发送结果状态
    public function getV_result_code($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('sms_result_code',($val!==null ? $val : $this->result_code));
    }
    
    // 获取用户
    public function getUser()
    {
        return $this->hasOne(\webadmin\modules\authority\models\AuthUser::className(), ['id' => 'user_id']);
    }
}
