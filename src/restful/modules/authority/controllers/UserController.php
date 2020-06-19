<?php
/**
 * 模型对象 \apiadmin\authority\models\AuthUser 的操作方法
 */
namespace apiadmin\authority\controllers;

use Yii;

class UserController extends \webadmin\restful\AController
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
     * 定义基于控制器进行操作的模型
     */
    public $modelClass = '\apiadmin\authority\models\AuthUser';
    
    /**
     * 定义默认的控制的操作方法
     */
    public function actions()
    {
        return [
            // 登录
            'login' => [
                'class' => '\webadmin\restful\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'findModel' => [$this,'findByLogin'],
            ],
            // 登出
            'logout' => [
                'class' => '\webadmin\restful\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'findModel' => [$this,'findByLogout'],
            ],
            // 获取当前登录的用户包含的权限数据树形结构
            'treeAuths' => [
                'class' => '\webadmin\restful\TreeAction',
                'modelClass' => '\apiadmin\authority\models\AuthAuthority',
                'checkAccess' => [$this, 'checkAccess'],
                'colOrder' => 'paixu desc',
                'colWhere' => function(){ return ['id'=>Yii::$app->user->identity->authorithIds]; },
                'colChilds' => 'childs',
                'colChildsWhere' => function(){ return [
                    'childs' => function($query){
                    $query->andWhere(['id'=>Yii::$app->user->identity->authorithIds])
                    ->with([
                        'childs' => function($query){
                        $query->andWhere(['id'=>Yii::$app->user->identity->authorithIds])
                        ->with([
                            'childs' => function($query){
                            $query->andWhere(['id'=>Yii::$app->user->identity->authorithIds])
                            ->with([
                                'childs' => function($query){ $query->andWhere(['id'=>Yii::$app->user->identity->authorithIds]);}
                            ]);
                            }
                            ]);
                        },
                        ]);
                    },
                ]; },
            ],
            // 修改个人资料
            'update' => [
                'class' => '\webadmin\restful\UpdateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
        ];
    }
    
    /**
     * 登录操作
     */
    public function findByLogin(){
        $modelClass = $this->modelClass;
        
        $login_name = Yii::$app->request->getBodyParam('login_name',Yii::$app->getRequest()->get('login_name'));
        $password = Yii::$app->request->getBodyParam('password',Yii::$app->getRequest()->get('password'));
        if(empty($login_name) || empty($password)) throw new \yii\web\HttpException(200,Yii::t('authority', '用户名和密码不能为空.'));
        
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
    
    /**
     * 登出操作
     */
    public function findByLogout(){
        $modelClass = $this->modelClass;
        $model = Yii::$app->user->identity;
        if($model instanceof $modelClass){
            $model->access_token = '';
            $model->save(false);
            
            return $model;
        }else{
            throw new \yii\web\HttpException(200,Yii::t('authority', '不允许操作的用户对象信息.'));
        }
    }
}
