<?php
/**
 * 数据库表 "sys_pinyin" 的模型对象.
 * @property string $id 流水号
 * @property string $word 中文
 * @property string $first 首字母
 * @property string $py 拼音
 * @property int $mpy mpy
 * @property int $mpy_s mpy_s
 * @property string $mpy_str 音调
 */

namespace webadmin\modules\config\models;

use Yii;

class SysPinyin extends \webadmin\ModelCAR
{
    /**
     * 返回数据库表名称
     */
    public static function tableName()
    {
        return 'sys_pinyin';
    }

    /**
     * 返回属性规则
     */
    public function rules()
    {
        return [
            [['word', 'first', 'py', 'mpy', 'mpy_s', 'mpy_str'], 'required'],
            [['mpy', 'mpy_s'], 'integer'],
            [['word'], 'string', 'max' => 3],
            [['first'], 'string', 'max' => 10],
            [['py'], 'string', 'max' => 30],
            [['mpy_str'], 'string', 'max' => 50],
            [['word'], 'unique'],
        ];
    }

    /**
     * 返回属性标签名称
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('config', '流水号'),
            'word' => Yii::t('config', '中文'),
            'first' => Yii::t('config', '首字母'),
            'py' => Yii::t('config', '拼音'),
            'mpy' => Yii::t('config', 'mpy'),
            'mpy_s' => Yii::t('config', 'mpy_s'),
            'mpy_str' => Yii::t('config', '音调'),
        ];
    }
    
    /**
     * 根据输入中文计算首拼音
     */
    public static function firstPinYin($zh='')
    {
        $zhArr = self::split_zh(trim($zh));
        if($zhArr){
            $list = self::find()->andFilterWhere(['word'=>$zhArr])->all();
            $pys = \yii\helpers\ArrayHelper::map($list,'word','first');
            foreach($zhArr as $k=>$v){
                if(isset($pys[$v])){
                    // 多音字, 首字母 逗号分开 取第一个 如: 否 f,p
                    list($_f) = explode(',', $pys[$v]);
                    $zhArr[$k] = $_f;
                }
            }
            $str = implode('',$zhArr);
            return strtoupper($str);
        }
        return null;
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
}
