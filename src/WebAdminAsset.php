<?php
/**
 * 定义管理后台使用的资源类
 */
namespace webadmin;

use \Yii;

class WebAdminAsset extends \yii\web\AssetBundle
{
    //public $sourcePath = '@webadmin/themes/beyond/assets';
    public $basePath = '@assetPath';
	public $baseUrl = '@assetUrl';	
    public $css = [
        'css/bootstrap.min.css',
        'css/font-awesome.min.css',
        //'css/css@family=open+sans_3a300italic,400italic,600italic,700italic,400,600,700,300.css', // 字体
        'css/beyond.min.css',
        'css/animate.min.css', // 动画
        'css/custom.css',
    ];
    public $js = [
        'js/skins.min.js',
        //'js/jquery.min.js', // jq装载到公共类库
        'js/bootstrap.min.js',
        'js/slimscroll/jquery.slimscroll.min.js',
        'js/beyond.min.js',
        'js/toastr/toastr.js',
        'js/bootbox/bootbox.js',
        'js/custom.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
    
    public function init()
    {
        /**
         * AJAX请求时，不进行资源输出
         */
        if(Yii::$app->request->isAjax){
            $this->css = $this->js = $this->depends = [];
        }elseif(($skins = Yii::$app->request->get('skins')) && file_exists(Yii::getAlias("@assetPath/css/skins/{$skins}.min.css"))){
            $this->css[] = "css/skins/{$skins}.min.css";
        }
        
        return parent::init();
    }
}
