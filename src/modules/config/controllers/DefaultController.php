<?php
/**
 * 默认的一些配置方法
 */ 
namespace webadmin\modules\config\controllers;

use Yii;

class DefaultController extends \webadmin\BController
{
    /**
     * 默认的一些方法不受权限控制
     */
    public $isAccessToken = false;
    
    /**
     * 不做csrf校验
     */
    public $enableCsrfValidation = false;
    
    /**
     * 根据中文内容取拼音首字母
     */
    public function actionPinyin()
    {
        $zh = trim(Yii::$app->request->get('zh'));
        return ($zh ? \webadmin\modules\config\models\SysPinyin::firstPinYin($zh) : null);
    }
    
    /**
     * 继承
     */
    public function actions()
    {
        return [
            // 城市查询
            'sys-region' => [
                'class' => '\webadmin\Select2Action',
                'className' => '\webadmin\modules\config\models\SysRegion',
                'col_id' => 'id',
                'col_text' => 'name',
                'col_v_text' => 'v_name',
                'col_where' => ['level'=>2],
            ],
        ];
    }
    
    // dropzone单文件上传
    public function actionDropzoneUpload()
    {
        $thumb = \yii\web\UploadedFile::getInstanceByName('file');
        $path = Yii::$app->request->get('path');
        if ($thumb) {
            $exts = ["jpg", "jpeg", "png", "gif", "bak", "zip", "rar", 
                "doc", "docx", "xls", "xlsx", "ppt", "pptx"]; // 服务器上允许上传的文件类型
            if (in_array($thumb->extension, $exts)) {
                $path = (defined('UP_PATH') ? UP_PATH : 'upfile/').
                        ($path ? $path.'/' : '') . 
                        date('Y').'/'.date('md').'/'.
                        time().mt_rand(1000,9999).'.'.$thumb->extension;
                        \yii\helpers\FileHelper::createDirectory(dirname($path));
                $thumb->saveAs(Yii::getAlias('@webroot').'/'.$path);
                echo $path;
                exit;
            }
        }
        exit;
    }
}
