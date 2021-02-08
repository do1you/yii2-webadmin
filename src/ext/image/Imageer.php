<?php
namespace webadmin\ext\image;

require_once __DIR__.'/Image.php';
use Image;
use Yii;
use common\models\Helpfn;
use Exception;

/**
 * Description of CImageComponent
 *
 * @author Administrator
 */
class Imageer
{
    /**
     * Drivers available:
     *  GD - The default driver, requires GD2 version >= 2.0.34 (Debian / Ubuntu users note: Some functions, eg. sharpen may not be available)
     *  ImageMagick - Windows users must specify a path to the binary. Unix versions will attempt to auto-locate.
     * @var string
     */
    public $driver = 'GD';

    /**
     * ImageMagick driver params
     * @var array
     */
    public $params = array();

    public function init()
    {
        parent::init();
        if($this->driver != 'GD' && $this->driver != 'ImageMagick'){
            throw new Exception('driver must be GD or ImageMagick');
        }
    }

    public function load($image)
    {
        $config = array(
            'driver'=>$this->driver,
            'params'=>$this->params,
        );
        return new Image($image, $config);
    }
    
    // 格式化图片
    public function getpic($pic='',$width=null,$height=null,$master=null)
    {
        $uppath = (defined('UP_PATH') ? UP_PATH : 'upfile/'); // 上传目录
        $rootpath = Yii::getAlias('@app/web/'); // 根目录 
        $urlPre = isset(Yii::$aliases['@web']) ? Yii::getAlias('@web/') : '';

        $arrs = $pic ? explode(".",$pic) : array();
        $ext = $arrs ? end($arrs) : '';
        if($pic && in_array(strtolower($ext),array('gif','jpg','png','jpeg'))){
            // 取远程图片
            if(substr($pic,0,7)=='http://' || substr($pic,0,8)=='https://'){
                if($width || $height){
                    $src = trim($uppath,'/').'/thumb/intel/'.md5($pic).'_'.basename($pic);
                    if(Helpfn::add_dir(dirname($rootpath.$src))){
                        if(file_exists($rootpath.$src) && is_file($rootpath.$src)){
                            $pic = $src;
                        }else{
                            $data = @file_get_contents($pic);
                            if(!$data || !@file_put_contents($rootpath.$src,$data)){
                                return $pic;
                            }else{
                                $pic = $src;
                            }
                        }
                    }else{
                        return $pic;
                    }
                }else{
                    return $pic;
                }
            }
            
            if($width || $height){
                $filepath = substr($pic,0,1)=='/' ? Helpfn::getDocumentRootPath().substr($pic,1) : $rootpath.$pic;
                if(!file_exists($filepath)) $filepath = $rootpath.'themes/images/nopic.jpg';
                
                $newsrc = trim($uppath,'/').'/thumb/'.trim(dirname($pic),'/').'/'.$width.'_'.$height.'_'.$master.'_'.basename($filepath);
                if(Helpfn::add_dir(dirname($rootpath.$newsrc))){
                    try{
                        if(is_file($rootpath.$newsrc) || $this->load($filepath)->resize($width,$height,$master)->save( $rootpath.$newsrc )){
                            $pic = $newsrc;
                        }
                    }catch(Exception $e) {
                        return $pic;
                    }
                }
            }elseif(substr($pic,0,1)=='/'){
                $pic = substr($pic,strlen(Yii::app()->request->baseUrl.'/'));
            }
            
            return $urlPre.$pic;
        }else{
            if($pic) return $pic;
            $src = 'themes/images/nopic.jpg';
            if($width || $height){
                $newsrc = trim($uppath,'/').'/thumb/'.dirname($src).'/'.$width.'_'.$height.'_'.$master.'_'.basename($src);
                if(Helpfn::add_dir(dirname($rootpath.$newsrc))){
                    try{
                        if(is_file($rootpath.$newsrc) || $this->load($rootpath.$src)->resize($width,$height,$master)->save( $rootpath.$newsrc )){
                            $src = $newsrc;
                        }
                    }catch(Exception $e) {
                        return $pic;
                    }
                }
            }
            
            return $urlPre.$src;
        }
    }
}
