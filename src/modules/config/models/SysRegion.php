<?php
/**
 * 数据库表 "sys_region" 的模型对象.
 * @property int $id ID
 * @property string $no 区域编码
 * @property string $name 区域名称
 * @property string $parent_no 上级区域
 * @property string $code 电话区号
 * @property int $level 区域级别
 * @property string $typename 级别名称
 * @property string $py_szm 首字母
 */

namespace webadmin\modules\config\models;

use Yii;

class SysRegion extends \webadmin\ModelCAR
{
    use \webadmin\TreeTrait;
    
    public function init()
    {
        $this->col_id = 'no';
        $this->col_parent = 'parent_no';
        $this->col_name = 'name';
        $this->col_value = 'id';
        $this->col_sort = 'code';
        
        return parent::init();
    }
    
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'sys_region';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['no', 'name', 'parent_no', 'code', 'level', 'typename', 'py_szm'], 'required'],
            [['level'], 'integer'],
            [['no', 'parent_no'], 'string', 'max' => 100],
            [['name'], 'string', 'max' => 255],
            [['code'], 'string', 'max' => 150],
            [['typename'], 'string', 'max' => 50],
            [['py_szm'], 'string', 'max' => 1],
            [['no'], 'unique'],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('config', 'ID'),
            'no' => Yii::t('config', '区域编码'),
            'name' => Yii::t('config', '区域名称'),
            'parent_no' => Yii::t('config', '上级区域'),
            'code' => Yii::t('config', '电话区号'),
            'level' => Yii::t('config', '区域级别'),
            'typename' => Yii::t('config', '级别名称'),
            'py_szm' => Yii::t('config', '首字母'),
        ];
    }
    
    public function getV_name($spec='')
    {
        return implode($spec,$this->parentNames);
    }
    
    // 获取父级名称
    public function getParentNames()
    {
        $names = array($this->name);
        if($this->parent){
            $names = array_merge($this->parent['parentNames'],$names);
        }
        return $names;
    }
}
