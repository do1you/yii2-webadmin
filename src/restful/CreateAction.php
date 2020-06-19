<?php
/**
 * 基于rest接口请求封装通用的创建模型方法
 * 目前是用继承YII2内置的rest服务进行自由的接口业务封包
 */
namespace webadmin\restful;

use Yii;
use yii\base\Model;
use yii\web\ServerErrorHttpException;

class CreateAction extends \webadmin\restful\Action
{
    /**
     * 定义数据更新的场景
     * @var string
     */
    public $scenario = Model::SCENARIO_DEFAULT;
    
    /**
     * 执行添加模型的业务逻辑
     * @return Model
     */
    public function run()
    {
        if($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        $model = new $this->modelClass([
            'scenario' => $this->scenario,
        ]);

        $model->load(Yii::$app->request->getBodyParams(), '');
        if ($model->save()) {
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $model = $this->findModel($id);
        }elseif(!$model->hasErrors()) {
            throw new ServerErrorHttpException(Yii::t('common', '创建对象失败，原因未知，请联系管理员.'));
        }

        return $model;
    }
}
