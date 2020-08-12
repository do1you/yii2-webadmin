<?php
/**
 * 模型对象SysModules的增删改查控制器方法.
 */ 
namespace webadmin\modules\config\controllers;

use Yii;
use webadmin\modules\config\models\SysModules;
use yii\data\ActiveDataProvider;

class SysModulesController extends \webadmin\BController
{
    public function init()
    {
        parent::init();
        
        // 初始化模块
        $dirs = \yii\helpers\FileHelper::findDirectories(Yii::getAlias('@module'), ['recursive' => false]);
        $list = \yii\helpers\ArrayHelper::map(SysModules::model()->find()->all(), 'code', 'v_self');
        foreach($dirs as $dir){
            $dir = basename($dir);
            if($dir=='home') continue;
            if(!isset($list[$dir])){
                $model = new SysModules;
                $model->code = $model->name = $dir;
                $model->save(false);
            }else{
                unset($list[$dir]);
            }
        }
        
        // 删除多余模块
        if($list){
            foreach($list as $item){
                $item->delete();
            }
        }
    }
    
	// 执行前
    public function beforeAction($action){
        Yii::$app->controller->pageTitle = Yii::t('config', '模块管理');
		Yii::$app->controller->currNav[] = Yii::$app->controller->pageTitle;
		
        return parent::beforeAction($action);
    }
    
    /**
     * 列表
     */
    public function actionIndex()
    {
    	unset(Yii::$app->session[$this->id]);
		$model = new SysModules();
        $dataProvider = $model->search(Yii::$app->request->queryParams);

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
     * 安装模块
     */
    public function actionCreate($id)
    {
        $model = $this->findModel($id);
        $sqlPath = $model['v_path']."install.sql";
        if(file_exists($sqlPath) && ($sql=file_get_contents($sqlPath))){
            $sql = trim($sql);
            $sql = str_replace("{module}",$model['code'],$sql);
            $sql &&  Yii::$app->db->createCommand($sql)->execute();
            Yii::$app->session->setFlash('success',$model['name'].Yii::t('config', '模块安装成功'));
        }else{
            Yii::$app->session->setFlash('warning',$model['name'].Yii::t('config', '模块安装成功，未能执行安装脚本install.sql'));
        }
        
        $model->state = '1';
        $model->save(false);
        
        Yii::$app->cache->flush(); // 更新缓存
        return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
    }

    /**
     * 卸载模块
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $sqlPath = $model['v_path']."uninstall.sql";
        if(file_exists($sqlPath) && ($sql=file_get_contents($sqlPath))){
            $sql = trim($sql);
            $sql = str_replace("{module}",$model['code'],$sql);
            $sql && Yii::$app->db->createCommand($sql)->execute();
            Yii::$app->session->setFlash('success',$model['name'].Yii::t('config', '模块成功卸载'));
        }else{
            Yii::$app->session->setFlash('warning',$model['name'].Yii::t('config', '模块成功卸载，缺少卸载脚本uninstall.sql，数据库将永久保留'));
        }
        $model->state = '0';
        $model->save(false);
        
        Yii::$app->cache->flush(); // 更新缓存
        return $this->redirect(!empty(Yii::$app->session[$this->id]) ? Yii::$app->session[$this->id] : ['index']);
    }

    /**
     * 查找模型
     */
    protected function findModel($id)
    {
        if (($model = SysModules::findOne($id)) !== null) {
            return $model;
        }

        throw new \yii\web\NotFoundHttpException(Yii::t('common','您查询的模型对象不存在'));
    }
}
