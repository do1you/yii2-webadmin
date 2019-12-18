<?php
/**
 * 模型对象SysLdItem的增删改查控制器方法.
 */ 
namespace webadmin\modules\config\controllers;

use Yii;
use webadmin\modules\config\models\SysLdItem;
use yii\data\ActiveDataProvider;

class SysLdItemController extends \webadmin\BController
{
	// 执行前
    public function beforeAction($action){
        Yii::$app->controller->pageTitle = Yii::t('config', '数据字典');
		Yii::$app->controller->currNav[] = Yii::$app->controller->pageTitle;
		
        return parent::beforeAction($action);
    }
    
    /**
     * 列表
     */
    public function actionIndex()
    {
    	unset(Yii::$app->session[$this->id]);
		$model = new SysLdItem();
        $dataProvider = $model->search(Yii::$app->request->queryParams,['parent_id'=>'0']);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }
    
    /**
     * 树型数据
     */
    public function actionTree()
    {
        $id = Yii::$app->request->get('id');
        if(empty($id)){
            throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
        }
        Yii::$app->session[$this->id] = ['index','id'=>$id];
        return $this->render('tree', [
            'model' => $this->findModel($id),
            'treeData' => SysLdItem::treeData($id),
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
        $model = new SysLdItem();
        $model->loadDefaultValues();
        $model->parent_id = Yii::$app->request->get('id');
        $model->setScenario($model->parent_id>0 ? 'ddchild' : 'ddparent');

        if($model->scenario=='ddchild'){ // 批量添加选项
            $transaction = SysLdItem::getDb()->beginTransaction(); // 使用事务关联
            
            if($model->load(Yii::$app->request->post()) && $model->ajaxValidation()){
                $list = explode("\n",$model['value']);
                $num = 0;
                foreach($list as $data){
                    $data = trim($data);
                    if($data){
                        $arr = explode("|",$data);
                        $newModel = clone $model;
                        $newModel->value = trim($arr[0]);
                        $newModel->name = !empty($arr[1]) ? trim($arr[1]) : trim($arr[0]);
                        if($newModel->save()){
                            $num++;
                        }
                    }
                }
                
                if($num>0){
                    $transaction->commit(); // 提交事务
                    Yii::$app->session->setFlash('success',Yii::t('common', '对象信息添加成功'));
                    return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
                }else{
                    Yii::$app->session->setFlash('error',Yii::t('common', '没有可添加的选项信息'));
                }
            }
        }else{
            if ($model->load(Yii::$app->request->post()) && $model->ajaxValidation() && $model->save()) {
            	Yii::$app->session->setFlash('success',Yii::t('common', '对象信息添加成功'));
                return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
            }
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
        $model->setScenario($model->parent_id>0 ? 'ddchild' : 'ddparent');

        if ($model->load(Yii::$app->request->post()) && $model->ajaxValidation() && $model->save()) {
        	Yii::$app->session->setFlash('success',Yii::t('common', '对象信息修改成功'));
            return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
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
        if($id && ($models = SysLdItem::findAll($id))){
            $transaction = SysLdItem::getDb()->beginTransaction(); // 使用事务关联
            foreach($models as $model){
                $model->delete();
            }
            $transaction->commit(); // 提交事务
        	Yii::$app->session->setFlash('success',Yii::t('common', '对象信息删除成功'));
        }else{
        	Yii::$app->session->setFlash('error',Yii::t('common', '需要删除的对象信息不存在'));
        }
        
        return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
    }

    /**
     * 查找模型
     */
    protected function findModel($id)
    {
        if (($model = SysLdItem::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
    }
}
