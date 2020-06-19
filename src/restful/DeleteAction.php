<?php
/**
 * 基于rest接口请求封装通用的删除模型的方法
 * 目前是用继承YII2内置的rest服务进行自由的接口业务封包
 */
namespace webadmin\restful;

use Yii;
use yii\web\ServerErrorHttpException;

class DeleteAction extends \webadmin\restful\Action
{
    /**
     * 执行删除模型的业务逻辑
     * @return Model
     */
    public function run()
    {
        if ($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }
        
        $id = Yii::$app->request->getBodyParam('id',Yii::$app->request->get('id'));
        
        // 兼容逗号间隔上传主键
        if(is_string($id) && stripos($id,',')!==false){
            $id = explode(',',$id);
        }
        
        $models = $this->findAllModel($id);
        $resultNum = 0;
        $resultList = [];
        foreach($models as $model){
            if($model->delete()) {
                $resultNum++;
                $resultList[] = $model;
            }
        }

        if($resultNum>0){
            return [
                'list' => $resultList,
                'result' => $resultNum,
            ];
        }else{
            throw new \yii\web\HttpException(200,Yii::t('common', '没有可以删除的模型对象记录.'));
        }
    }
}
