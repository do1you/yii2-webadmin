<?php
/**
 * 格式化restful数据输出
 */
namespace webadmin\restful;

use Yii;

class Serializer extends \yii\rest\Serializer
{
    /**
     * 模型对数据内容出错时输出错误信息
     */
    protected function serializeModelErrors($model)
    {
        $messages = $result = [];
        foreach ($model->getFirstErrors() as $name => $message) {
            $result[] = [
                'field' => $name,
                'message' => $message,
            ];
            $messages[] = $message;
        }
        
        return [
            'name' => 'Data Validation Failed.',
            'message' => implode(' ', $messages),
            'status' => 422,
            'info' => $result,
        ];
    }
}