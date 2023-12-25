<?php
/**
 * 数据密钥锁，若是存储字段有长度限制，加密的字段内容需要少于长度的四分之一
 */

namespace webadmin\behaviors;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\db\BaseActiveRecord;
use yii\base\InvalidCallException;
use yii\validators\NumberValidator;
use yii\helpers\ArrayHelper;


class SecretKeyLockBehavior extends AttributeBehavior
{
    /**
     * 定义的密钥赋值
     */
    public $value;
    
    /**
     * 是否跳过更新
     */
    public $skipUpdateOnClean = false;
    
    /**
     * 密钥锁字段
     */
    public $lockAttribute;
    
    /**
     * 通过密钥锁进行加密的字段
     */
    public $secretAttribute;
    
    /**
     * 密钥锁的加密密钥
     */
    public $secretKey;
    
    /**
     * 属于数字的字段，避免数字后有.00的问题
     */
    public $numberAttribute;
    
    /**
     * 分割符
     */
    public $splitCol = '|*|';
    
    
    /**
     * 开始监听事件
     */
    public function attach($owner)
    {
        parent::attach($owner);
        
        if (empty($this->attributes)) {
            $lock = $this->getLockAttribute();
            $this->attributes = array_fill_keys(array_keys($this->events()), $lock);
        }
        
        if (empty($this->secretKey)) {
            $params = Yii::$app->params;
            $this->secretKey = (isset($params['secretKey']) ? $params['secretKey'] : 'Yllt9182555~!~');
        }
    }
    
    /**
     * 定义事件执行方法
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'evaluateAttributes',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'evaluateAttributes',
            BaseActiveRecord::EVENT_BEFORE_DELETE => 'evaluateAttributes',
        ];
    }
    
    /**
     * 返回密钥锁字段
     */
    public function getLockAttribute()
    {
        return $this->lockAttribute;
    }
    
    /**
     * 返回通过密钥锁进行加密的字段
     */
    protected function getSecretAttribute()
    {
        return $this->secretAttribute;
    }
    
    /**
     * 获取密钥锁信息的值
     */
    protected function getValue($event)
    {
        if ($this->value === null) {
            return $this->getSecretKey();
        }
        
        return parent::getValue($event);
    }
    
    /**
     * 计算密钥锁信息
     */
    public function getSecretKey()
    {
        return \webadmin\ext\Helpfn::encrypt($this->getSecretCol(), 'E', $this->secretKey);
    }
    
    /**
     * 计算旧数据的密钥锁信息
     */
    public function getOldSecretKey()
    {
        return \webadmin\ext\Helpfn::encrypt($this->getSecretCol(true), 'E', $this->secretKey);
    }
    
    /**
     * 解锁密钥锁信息
     */
    public function getDeSecretKey()
    {
        $lock = $this->getLockAttribute();
        $owner = $this->owner;
        if($owner->hasAttribute($lock)!==false){
            return \webadmin\ext\Helpfn::encrypt($owner->getOldAttribute($lock), 'D', $this->secretKey);
        }else{
            return false;
        }
        
    }
    
    /**
     * 密钥锁的加密字段
     */
    public function getSecretCol($isOld = false)
    {
        $resp = [];
        $secretAttribute = $this->getSecretAttribute();
        if($secretAttribute){
            $owner = $this->owner;
            $attributes = $isOld ? $owner->getOldAttributes() : $owner->getAttributes();
            $secretAttribute = (array) $secretAttribute;
            $numberAttribute = $this->numberAttribute ? (array) $this->numberAttribute : [];
            foreach($secretAttribute as $attribute){
                if($owner->hasAttribute($attribute)!==false){
                    $val = isset($attributes[$attribute]) ? $attributes[$attribute] : ($isOld ? '' : $owner->getOldAttribute($attribute));
                    if($numberAttribute && in_array($attribute, $numberAttribute)){
                        $val = round($val*100000)/100000;
                    }
                    $resp[] = (string) $val;
                }
            }
        }
        return implode($this->splitCol, $resp);
    }
    
    /**
     * 更新当前的密钥锁信息
     */
    public function upgrade()
    {
        $owner = $this->owner;
        $lock = $this->getLockAttribute();
        $version = $this->getSecretKey();
        $owner->updateAttributes([$lock => $version]);
    }
}

