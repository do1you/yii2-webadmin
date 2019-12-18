<?php
/**
 * 模型对象 webadmin\modules\authority\models\AuthUser 的操作方法
 */
namespace webadmin\modules\authority\controllers;

use Yii;

class UserController extends \webadmin\BController
{
    // 执行前
    public function beforeAction($action)
    {
        if(in_array($action->id,['login'])){
            $this->isAccessToken = false;
        }
        return parent::beforeAction($action);
    }
    
    /**
     * 用户首页
     */
    public function actionIndex()
    {
        if (!YII_ENV_PROD){
            return $this->render('demo');
        }else{
            return $this->render('index');
        }
    }
    
    /**
     * 更新缓存
     */
    public function actionClearcache()
    {
        Yii::$app->cache->flush();
        
        unset(Yii::$app->session['pageNumArr'],Yii::$app->session['searchWhereArr'],Yii::$app->session['searchQuestUrl']);
        
        Yii::$app->session->setFlash('success',Yii::t('authority', '缓存更新成功'));
        if(!empty($_SERVER['HTTP_REFERER'])){
            $this->redirect($_SERVER['HTTP_REFERER']);
        }else{
            $this->redirect(\yii\helpers\Url::toRoute('/authority/user/index'));
        }
    }
    
    /**
     * 登录操作
     */
    public function actionLogin()
    {
        if( !Yii::$app->user->isGuest ) { // 已登录
            $this->redirect(\yii\helpers\Url::toRoute('index'));
        }
        
        $model = new \webadmin\modules\authority\models\AuthUser();

        if( isset($_POST['AuthUser']) ){
            $model->load(Yii::$app->request->post());
            $login_name = Yii::$app->request->getBodyParam('AuthUser')['login_name'];
            $password = Yii::$app->request->getBodyParam('AuthUser')['password'];

            if(empty($login_name) || empty($password)){
                Yii::$app->session->setFlash('error',Yii::t('authority', '用户名和密码不能为空.'));
            }elseif(($nModel = \webadmin\modules\authority\models\AuthUser::findByUsername($login_name))){
                if($nModel->validatePassword($password) && Yii::$app->user->login($nModel,86400)){
                    // 登录成功
                    $url = Yii::$app->user->getReturnUrl(\yii\helpers\Url::toRoute('index'));
                    if(strlen($url)>150 || stripos($url,Yii::$app->request->baseUrl)===false) $url = \yii\helpers\Url::toRoute('index');
                }else{
                    Yii::$app->session->setFlash('error',Yii::t('authority', '用户密码不正确.'));
                }
            }else{
                Yii::$app->session->setFlash('error',Yii::t('authority', '用户信息不存在.'));
            }
            
            // 记录登录日志
            \webadmin\modules\logs\models\LogUserLogin::insertion([
                'username' => $login_name,
                'modules' => ($this->module ? $this->module->id : null),
                'addtime' => date('Y-m-d H:i:s'),
                'ip' => Yii::$app->request->userIP,
                'result' => (!empty($url) ? '0' : '1'),
            ]);
            
            !empty($url) && $this->redirect($url);
        }elseif(!empty($_SERVER['HTTP_REFERER'])){
            Yii::$app->user->returnUrl = $_SERVER['HTTP_REFERER'];
        }
        
        $this->layout='/html5';
        $this->body_class='login_box bg'.rand(1,13);
        return $this->render('login',array('model'=>$model));
    }
    
    // 退出页面
	public function actionLogout()
	{
	    Yii::$app->user->logout();	
	    $this->redirect(\yii\helpers\Url::toRoute('index'));
	}
	
	/**
	 * 修改密码
	 */
	public function actionPassword()
	{
	    $this->pageTitle = $this->currNav = Yii::t('authority', "修改密码");
	    
	    $model = Yii::$app->user->identity;
	    if(!$model) $this->actionLogout();
	    $model->setScenario('password');
	    
	    $model->password_curr = $model->password; 
	    $model->password = '';
	    $data = Yii::$app->request->post();	    
	    if(isset($data['AuthUser'])){
	        if($model->load($data) && $model->ajaxValidation() && $model->validate()) {
	            $model->setPassword($model->password);
	            $model->save(false);
	            Yii::$app->session->setFlash('success',Yii::t('authority', '密码修改成功'));
	            return $this->redirect(\yii\helpers\Url::toRoute('password'));
	        }
	    }
	    
	    return $this->render('password',array(
	        'model'=>$model,
	    ));
	}
	
	/**
	 * 修改资料
	 */
	public function actionInfo()
	{
	    $this->pageTitle = $this->currNav = Yii::t('authority', "修改个人信息");
	    
	    $model = Yii::$app->user->identity;
	    if(!$model) $this->actionLogout();
	    $model->setScenario('info');
	    
	    if ($model->load(Yii::$app->request->post()) && $model->ajaxValidation() && $model->save()) {
	        Yii::$app->session->setFlash('success',Yii::t('authority', '个人资料修改成功'));
	        return $this->redirect(\yii\helpers\Url::toRoute('info'));
	    }
	    
	    return $this->render('info',array(
	        'model'=>$model,
	    ));
	}
}
