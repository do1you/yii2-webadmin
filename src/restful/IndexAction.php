<?php
/**
 * 基于rest接口请求封装通用的列表方法
 * 目前是用继承YII2内置的rest服务进行自由的接口业务封包
 */
namespace webadmin\restful;

use Yii;
use yii\data\ActiveDataProvider;
use yii\data\DataFilter;

class IndexAction extends \webadmin\restful\Action
{
    /**
     * 采用自定义的数据驱动类
     * @var ActiveDataProvider
     */
    public $prepareDataProvider;
    
    /**
     * 自定义数据过滤驱动类
     * @var DataFilter
     */
    public $dataFilter;


    /**
     * 执行列表的业务逻辑
     * @return ActiveDataProvider
     */
    public function run()
    {
        // 进行接口权限判断
        if($this->checkAccess) {
            call_user_func($this->checkAccess, $this->id);
        }

        return $this->prepareDataProvider();
    }

    /**
     * 返回符合条件请求模型集合的数据驱动类
     * @return ActiveDataProvider
     */
    protected function prepareDataProvider()
    {
        $requestParams = Yii::$app->getRequest()->getBodyParams();
        if (empty($requestParams)) {
            $requestParams = Yii::$app->getRequest()->getQueryParams();
        }
        
        $modelClass = $this->modelClass;

        $filter = null;
        if ($this->dataFilter !== null) {
            $this->dataFilter = Yii::createObject($this->dataFilter);
            
            // 从全局提取过滤条件
            $filterAttributeName = $this->dataFilter->filterAttributeName;
            if(!isset($requestParams[$filterAttributeName])){
                $model = new $modelClass;
                foreach($requestParams as $key=>$value){
                    if($model->hasAttribute($key) || $model->hasProperty($key)){
                        $requestParams[$filterAttributeName][$key] = $value;
                    }
                }
            }
            
            // 组装过滤条件
            if ($this->dataFilter->load($requestParams)) {
                $filter = $this->dataFilter->build(false);
                if ($filter === false) {
                    return $this->dataFilter;
                }
            }
        }

        if ($this->prepareDataProvider !== null) {
            return call_user_func($this->prepareDataProvider, $this, $filter, (isset($requestParams[$filterAttributeName]) ? $requestParams[$filterAttributeName] : []));
        }

        $query = $modelClass::find();
        if (!empty($filter)) {
            $query->andFilterWhere($filter);
        }

        return Yii::createObject([
            'class' => ActiveDataProvider::className(),
            'query' => $query,
            'pagination' => [
                'params' => $requestParams,
                'pageSizeParam' => 'perPage',
            ],
            'sort' => [
                'params' => $requestParams,
            ],
        ]);
    }
}
