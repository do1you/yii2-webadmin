<?php
/**
 * 模型对象AuthUser的增删改查控制器方法.
 */ 
namespace webadmin\modules\authority\controllers;

use Yii;
use webadmin\modules\authority\models\AuthUser;
use yii\data\ActiveDataProvider;

class AuthUserController extends \webadmin\BController
{
	// 执行前
    public function beforeAction($action){
        Yii::$app->controller->pageTitle = Yii::t('authority', '管理员管理');
		Yii::$app->controller->currNav[] = Yii::$app->controller->pageTitle;
		
        return parent::beforeAction($action);
    }
    
    /**
     * 列表
     */
    public function actionIndex()
    {
		$model = new AuthUser();
        $dataProvider = $model->search(Yii::$app->request->queryParams,[],'roleRels.role');

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }

    /**
     * 查看模型
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * 添加模型
     */
    public function actionCreate()
    {
        $model = new AuthUser();
        $model->loadDefaultValues();
        $model->setScenario('insert');

        if ($model->load(Yii::$app->request->post()) && $model->ajaxValidation() && $model->validate()) {
            $model->setPassword($model->password); // 密码加密
            $model->save(false);
        	Yii::$app->session->setFlash('success',Yii::t('common', '对象信息添加成功'));
            return $this->redirect(['index']);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * 修改模型
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->setScenario('update');

        if ($model->load(Yii::$app->request->post()) && $model->ajaxValidation() && $model->validate()) {
            if($model->password){
                $model->setPassword($model->password); // 密码加密
            }else{
                unset($model['password']); // 不修改密码
            }
            $model->save(false);
            
        	Yii::$app->session->setFlash('success',Yii::t('common', '对象信息修改成功'));
            return $this->redirect(['index']);
        }
        
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * 删除模型，支持批量删除
     */
    public function actionDelete()
    {
        $id = Yii::$app->request->getBodyParam('id',Yii::$app->getRequest()->getQueryParam('id'));
        if($id && ($models = AuthUser::findAll($id))){
            $transaction = AuthUser::getDb()->beginTransaction(); // 使用事务关联
            foreach($models as $model){
                $model->delete();
            }
            $transaction->commit(); // 提交事务
            Yii::$app->session->setFlash('success',Yii::t('common', '对象信息删除成功'));
        }else{
            Yii::$app->session->setFlash('error',Yii::t('common', '需要删除的对象信息不存在'));
        }
        return $this->redirect(['index']);
    }

    /**
     * 查找模型
     */
    protected function findModel($id)
    {
        if (($model = AuthUser::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
    }
}
