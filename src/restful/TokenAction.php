<?php
/**
 * 基于rest接口请求封装通用的详情方法
 * 目前是用继承YII2内置的rest服务进行自由的接口业务封包
 */
namespace webadmin\restful;

use Yii;

class TokenAction extends \webadmin\restful\Action
{
    
    /**
     * restful接口初始化
     */
    public function init()
    {
        
    }
    
    /**
     * 获取认证授权信息
     * @return Model
     */
    public function run()
    {
        $modelClass = Yii::$app->user->identityClass;
        
        $login_name = Yii::$app->request->getBodyParam('username',Yii::$app->getRequest()->get('username'));
        $password = Yii::$app->request->getBodyParam('password',Yii::$app->getRequest()->get('password'));
        if(empty($login_name) || empty($password))
            throw new \yii\web\HttpException(200,Yii::t('authority', '用户名和密码不能为空.'));
            
        if(($model = $modelClass::findByUsername($login_name))){
            if($model->validatePassword($password)){
                $model->access_token = md5(serialize($model).microtime());
                $model->save(false);
                
                return $model;
            }else{
                throw new \yii\web\HttpException(200,Yii::t('authority', '用户密码不正确.'));
            }
        }else{
            throw new \yii\web\HttpException(200,Yii::t('authority', '用户信息不存在.'));
        }
    }
}
