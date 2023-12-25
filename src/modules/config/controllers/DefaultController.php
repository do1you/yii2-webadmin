<?php
/**
 * 默认的一些配置方法
 */ 
namespace webadmin\modules\config\controllers;

use Yii;

class DefaultController extends \webadmin\BController
{
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
     * 获取文件下载成功了没
     */
    public function actionDown()
    {
        $result = [];
        
        $excelDownCache = Yii::$app->user->id ? \webadmin\modules\config\models\SysQueue::find()->where([
            'user_id' => Yii::$app->user->id,
            'state' => ['2', '3'],
            'callback' => 'excel',
        ])
        ->orderBy("id asc")
        ->all() : [];
        
        foreach($excelDownCache as $item){
            $params = json_decode($item['params'],true);
            $cacheName = $params ? \webadmin\ext\PhpExcel::exportCacheName($params[0],$params[1],$params[2],$params[3]) : '';
            $path = $cacheName ? \webadmin\ext\PhpExcel::cache()->get($cacheName) : '';
            if($path && file_exists($path)){
                if(stristr(PHP_OS, 'WIN')){
                    $encode = stristr(PHP_OS, 'WIN') ? 'GBK' : 'UTF-8';
                    $path = iconv($encode, 'UTF-8', $path); // 转码适应不同操作系统的编码规则
                }
                $shoFileName = preg_replace('/^.+[\\\\\\/]/', '', $path);
                $downUrl = \yii\helpers\Url::to(['/'.$params[0]]).'?'.(!empty($params[2]) ? $params[2].'&' : '').'is_export=2';
                $result['msg'] = "您刚才下载的文件（{$shoFileName}）已生成，请点击确定进行下载。";
                $result['url'] = $downUrl;
                break;
            }
        }
        return json_encode($result);
    }
    
    /**
     * 继承
     */
    public function actions()
    {
        return [
            // 城市查询
            'sys-region' => [
                'class' => '\webadmin\actions\Select2Action',
                'className' => '\webadmin\modules\config\models\SysRegion',
                'col_id' => 'id',
                'col_text' => 'name',
                'col_v_text' => 'v_name',
                'col_where' => ['level'=>2],
            ],
            // 城市包括省份
            'sys-region-province' => [
                'class' => '\webadmin\actions\Select2Action',
                'className' => '\webadmin\modules\config\models\SysRegion',
                'col_id' => 'id',
                'col_text' => 'name',
                'col_v_text' => 'v_name',
                'col_where' => ['level'=>[1,2]],
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
                return $path;
            }
        }
        return '';
    }
}
