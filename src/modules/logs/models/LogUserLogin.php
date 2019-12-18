<?php
/**
 * 数据库表 "log_user_login" 的模型对象.
 * @property int $id ID
 * @property string $username 用户名
 * @property string $modules 模块
 * @property string $addtime 时间
 * @property string $ip IP
 * @property int $result 结果
 */

namespace webadmin\modules\logs\models;

use Yii;

class LogUserLogin extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'log_user_login';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['username', 'modules', 'addtime', 'ip'], 'safe'],
            [['addtime'], 'safe'],
            [['result'], 'integer'],
            [['username'], 'string', 'max' => 80],
            [['modules'], 'string', 'max' => 50],
            [['ip'], 'string', 'max' => 23],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('logs', 'ID'),
            'username' => Yii::t('logs', '用户名'),
            'modules' => Yii::t('logs', '模块'),
            'addtime' => Yii::t('logs', '时间'),
            'ip' => Yii::t('logs', 'IP'),
            'result' => Yii::t('logs', '结果'),
        ];
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
        
        // 状态
        if(strlen($this->result)){
            $ddVal = array_search($this->result, $this->getV_result(false));
            $this->result = strlen($ddVal) ? $ddVal : $this->result;
        }
        
        $result = parent::search([],$wheres,$with,$joinWith);
        
        $this->load($params);
        
        return $result;
    }
    
    // 获取状态
    public function getV_result($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('action_states',($val!==null ? $val : $this->result));
    }
}
