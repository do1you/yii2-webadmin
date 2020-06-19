<?php
/**
 * 基于rest接口请求封装通用的更新模型的方法
 * 目前是用继承YII2内置的rest服务进行自由的接口业务封包
 */
namespace webadmin\restful;

use Yii;
use yii\base\Model;
use yii\web\ServerErrorHttpException;

class UpdateAction extends \webadmin\restful\Action
{
    /**
     * 定义数据更新的场景
     * @var string
     */
    public $scenario = Model::SCENARIO_DEFAULT;


    /**
     * 执行更新模型的业务逻辑
     * @return Model
     */
    public function run()
    {
        $id = Yii::$app->request->getBodyParam('id',Yii::$app->request->get('id'));
        
        $model = $this->findModel($id);
        if($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id, $model);
        }

        $model->scenario = $this->scenario;
        $model->load($parmas, '');
        if ($model->save() === false && !$model->hasErrors()) {
            throw new ServerErrorHttpException(Yii::t('common', '更新对象失败，原因未知，请联系管理员.'));
        }

        return $model;
    }
}
