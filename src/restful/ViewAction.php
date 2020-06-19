<?php
/**
 * 基于rest接口请求封装通用的详情方法
 * 目前是用继承YII2内置的rest服务进行自由的接口业务封包
 */
namespace webadmin\restful;

use Yii;

class ViewAction extends \webadmin\restful\Action
{
    /**
     * 执行获取模型详情的业务逻辑
     * @return Model
     */
    public function run()
    {
        $id = Yii::$app->request->getBodyParam('id',Yii::$app->request->get('id'));
        
        $model = $this->findModel($id);
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        return $model;
    }
}
