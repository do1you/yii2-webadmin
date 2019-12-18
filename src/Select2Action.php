<?php
/**
 * 配置化select2的选择器
 */
namespace webadmin;

use Yii;

class Select2Action extends \yii\base\Action
{
    /**
     * 类名称
     */
    public $className = null;
    
    /**
     * 主键字段
     */
    public $col_id = 'id';
    
    /**
     * 文本字段
     */
    public $col_text = 'name';
    
    /**
     * 文本显示字段
     */
    public $col_v_text = '';
    
    /**
     * 默认条件
     */
    public $col_where = null;
    
    /**
     * 默认排序
     */
    public $col_sort = null;
    
    /**
     * 初始化
     */
    public function init()
    {
        if(!$this->className){
            throw new \yii\web\NotFoundHttpException(Yii::t('common','您访问的页面不存在'));
        }
    }
    
    /**
     * 获取select2下拉的数据
     */
    public function run()
    {
        $id = Yii::$app->request->post('id',Yii::$app->request->get('id'));
        $q = Yii::$app->request->post('q',Yii::$app->request->get('q'));
        
        $className = $this->className;
        $key = $this->col_id;
        $text = $this->col_text;
        $query = class_exists($className) ? $className::find() : (new \yii\db\Query);
        
        $query->andFilterWhere(['like',$text,$q])
              ->andFilterWhere([$key=>$id]);
        
        if(!class_exists($className)){
            $query->select(["{$key} as id","{$text} as text"])
                  ->from($className);
        }
        
        $this->col_where && $query->andFilterWhere($this->col_where);
        $this->col_sort && $query->orderBy($this->col_sort);
        
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
        ]);
        
        $id && $dataProvider->setPagination(false);
        
        if(class_exists($className)){
            $result['items'] = [];
            foreach($dataProvider->getModels() as $m){
                $result['items'][] = [
                    'id' => $m[$key],
                    'text' => $m[$this->col_v_text ? $this->col_v_text : $text],
                ];
            }
        }else{
            $result['items'] = $dataProvider->getModels();
        }
        $result['total_page'] = $id ? 1 : $dataProvider->getPagination()->pageCount;

        return $result;
    }
}

