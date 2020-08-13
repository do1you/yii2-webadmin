<?php
/**
 * 数据库表 "sys_modules" 的模型对象.
 * @property int $id 流水号
 * @property string $name 模块名称
 * @property string $code 模块代码
 * @property string $memo 描述
 * @property int $state 状态 0未安装 1已安装
 * @property string $addtime 时间
 * @property int $is_system 是否系统模块
 */

namespace webadmin\modules\config\models;

use Yii;

class SysModules extends \webadmin\ModelCAR
{
    //是否记录数据库日志
    protected $isSaveLog = true;
    
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'sys_modules';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [[ 'memo', 'addtime'], 'safe'],
            [['name', 'code'], 'required'],
            [['state', 'is_system'], 'integer'],
            [['addtime'], 'safe'],
            [['name'], 'string', 'max' => 120],
            [['code'], 'string', 'max' => 50],
            [['memo'], 'string', 'max' => 255],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('config', '流水号'),
            'name' => Yii::t('config', '模块名称'),
            'code' => Yii::t('config', '模块代码'),
            'memo' => Yii::t('config', '描述'),
            'state' => Yii::t('config', '状态'), //  0未安装 1已安装
            'addtime' => Yii::t('config', '时间'),
            'is_system' => Yii::t('config', '是否系统模块'),
        ];
    }
    
    /**
     * 初始化加载模块配置
     */
    public static function initModule()
    {
		Yii::$app->setModules(self::validModules());
    }

	/**
     * 获取所有已安装的模块
     */ 
	public static function validModules()
	{
	    $cachekey = 'common/modulesList';
	    $result = Yii::$app->cache->get($cachekey);
	    if($result===false || $result===null){
			$result = \yii\helpers\ArrayHelper::map(self::find()->andWhere(['state' => '1'])->all(), 'code', 'code');
			foreach($result as $code=>$key){
			    $result[$code] = "module\{$code}\Module";
			}
	        Yii::$app->cache->set($cachekey,$result,86400);
	    }
	    return $result;	    
	}
    
    // 返回模块目录
    public function getV_path(){
        return (Yii::$app->basePath.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.$this->code.DIRECTORY_SEPARATOR);
    }
    
    // 获取状态
    public function getV_state($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('config_module_state',($val!==null ? $val : $this->state));
    }
    
    // 获取是否系统模块
    public function getV_is_system($val = null)
    {
        return \webadmin\modules\config\models\SysLdItem::dd('enum',($val!==null ? $val : $this->is_system));
    }
}
