<?php
/**
 * 模型对象SysConfig的增删改查控制器方法.
 */ 
namespace webadmin\modules\config\controllers;

use Yii;
use webadmin\modules\config\models\SysConfig;
use yii\data\ActiveDataProvider;

class SysConfigController extends \webadmin\BController
{
	// 执行前
    public function beforeAction($action){
        Yii::$app->controller->pageTitle = Yii::t('config', '参数配置管理');
		Yii::$app->controller->currNav[] = Yii::$app->controller->pageTitle;
		
		Yii::$app->controller->currUrl = $this->module->id.'/'.$this->id.'/config';

        return parent::beforeAction($action);
    }
    
    /**
     * 参数设置
     */
    public function actionConfig()
    {
        $model = new SysConfig();
        $model->group_id = Yii::$app->request->get('group_id');
        if($model->group_id) $this->currUrl = 'config/sys-config/config?group_id='.$model->group_id;
        $configList = SysConfig::find()
                    ->orderBy("reorder desc,label_name desc")
                    ->andFilterWhere(['state'=>'0','group_id'=>$model->v_group_obj_child])
                    ->all();
        $groupList = \yii\helpers\ArrayHelper::map($configList,'key','v_self','group_id');

        // 保存
        if(!empty($_POST['SysConfig']) && $this->action->id=='save-config'){
            $transaction = SysConfig::getDb()->beginTransaction(); // 使用事务
            
            $keyList = \yii\helpers\ArrayHelper::map($configList,'key','v_self');
            $errors = array();
            foreach($keyList as $item){
                if(isset($_POST['SysConfig'][$item['key']])){
                    $item->value = $_POST['SysConfig'][$item['key']];
                    if(!$item->save()){
                        $err = $item->getErrors();
                        foreach($err as $k=>$v){
                            $errors[] = $item['label_name'].implode(" ",$v);
                        }
                    }
                }
            }
            
            if(!empty($errors)){
                Yii::$app->session->setFlash('error',implode("<br>",$errors));
            }else{
                $transaction->commit(); // 提交事务
                
                Yii::$app->session->setFlash('success','更新信息成功');
                return $this->redirect(['save-config']);
            }
        }
        
        return $this->render('config',array(
            'model' => $model,
            'list' => $groupList,
        ));
    }
    
    /**
     * 修改参数设置
     */
    public function actionSaveConfig()
    {
        return $this->actionConfig();
    }
    
    /**
     * 下拉数据源
     */
    public function actionSelect2()
    {
        $id = Yii::$app->request->post('id',Yii::$app->request->get('id'));
        $k = Yii::$app->request->post('key',Yii::$app->request->get('key'));
        $q = Yii::$app->request->post('q',Yii::$app->request->get('q'));
        $model = $k ? SysConfig::findOne($k) : null;
        $result = ['items'=>[], 'total_count' => 0,];
        if(($model && ($arr=explode('.', $model['config_params'])) && count($arr)==3)
            || (($arr=explode('.', $k)) && count($arr)==3)
        ){
            list($table,$key,$text) = $arr;
            if($table && $key && $text){
                $wheres = ['or'];
                $qList = $q ? explode(',',str_replace(["，","\r\n","\n","\t"],",",$q)) : [];
                foreach($qList as $qItem){
                    $qItem = trim($qItem);
                    if(strlen($qItem)>0){
                        $wheres[] = ['like',$text,$qItem];
                    }
                }
                $limit = 20;
                $query = new \yii\db\Query();
                $query->select(["{$key} as id","{$text} as text"])
                      ->from($table)
                      //->andFilterWhere(['like',$text,$q]) // 调整为支持逗号间隔批量查询
                      ->andFilterWhere([$key=>$id]);
                count($wheres)>1 && $query->andFilterWhere($wheres);
                
                $dataProvider = new ActiveDataProvider([
                    'query' => $query,
                ]);
                
                $id && $dataProvider->setPagination(false);
                
                $result['items'] = $dataProvider->getModels();
                $result['total_page'] = $id ? 1 : $dataProvider->getPagination()->pageCount;
            }
        }
        return $result;
    }
    
    /**
     * 列表
     */
    public function actionIndex()
    {
    	unset(Yii::$app->session[$this->id]);
		$model = new SysConfig();
        $dataProvider = $model->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'model' => $model,
        ]);
    }
    
    /**
     * 树型数据
     */
    /*
    public function actionTree()
    {
    	Yii::$app->session[$this->id] = [$this->action->id];
        return $this->render('tree', [
            'treeData' => SysConfig::treeData(),
        ]);
    }
    */

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
        $model = new SysConfig();
        $model->loadDefaultValues();

        if ($model->load(Yii::$app->request->post()) && $model->ajaxValidation() && $model->save()) {
        	Yii::$app->session->setFlash('success',Yii::t('common', '对象信息添加成功'));
            return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
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
        if($id && ($models = SysConfig::findAll($id))){
            $transaction = SysConfig::getDb()->beginTransaction(); // 使用事务关联
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
        if (($model = SysConfig::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
    }
}
