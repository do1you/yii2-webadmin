<?php
/**
 * 内容格式化扩展
 */

namespace webadmin\ext;

use Yii;

class Formatter extends \yii\i18n\Formatter
{
    /**
     * 格式化内容
     */
    public function format($value, $format)
    {
        if ($format instanceof Closure) {
            return call_user_func($format, $value, $this);
        } elseif (is_array($format)) {
            if (!isset($format[0])) {
                throw new InvalidArgumentException('The $format array must contain at least one element.');
            }
            $f = $format[0];
            $format[0] = $value;
            $params = $format;
            $format = $f;
        } elseif(substr($format,0,3)=='dd_'){ // 扩展数据字典
            $params = [$value, [substr($format,3)]];
            $format = 'dd';
        } else {
            $params = [$value];
        }
        $method = 'as' . $format;
        if ($this->hasMethod($method)) {
            return call_user_func_array([$this, $method], $params);
        }
        
        throw new InvalidArgumentException("Unknown format type: $format");
    }
    
    /**
     * 数据字典
     */
    public function asDd($value, $options = [])
    {
        if ($value === null || empty($options[0])) {
            return $this->nullDisplay;
        }
        
        return \webadmin\modules\config\models\SysLdItem::dd($options[0], $value);
    }

}