<?php
/**
 * 安装脚本
 */
namespace webadmin\modules\config\controllers;

use Yii;

class InstallController extends \webadmin\BController
{
    public $isAccessToken = false;

    /**
     * 初始化安装
     */
    public function actionIndex()
    {
        // 已经安装
        if(file_exists(Yii::getAlias('@runtime/webadmin-install.lock'))){
            return $this->redirect(['/authority/user/login']);
        }
        
        // 执行库脚本
        $sqlPath = Yii::getAlias("@webadmin/modules/yalalatdb.sql");
        if(file_exists($sqlPath) && ($sql=file_get_contents($sqlPath))){
            $sql &&  Yii::$app->db->createCommand($sql)->execute();
            $sql = "select 123";
            file_put_contents(Yii::getAlias('@runtime/webadmin-install.lock'), '1');
            return $this->redirect(['/authority/user/login']);
        }else{
            throw new \yii\web\NotFoundHttpException(Yii::t('common','初始化失败！'));
        }
    }
}

