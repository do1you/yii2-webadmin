<?php
/**
 * 配置化select2的选择器
 */
namespace webadmin\actions;

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
     * 懒加载数据
     */
    public $model_withs = null;
    
    /**
     * 关联模型
     */
    public $join_withs = null;
    
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
        
        if(class_exists($className)){
            $this->model_withs && $query->with($this->model_withs);
            
            $this->join_withs && $query->joinWith($this->join_withs);
        }
        
        $wheres = ['or'];
        $qList = $q ? explode(',',str_replace("，",",",$q)) : [];
        if($text && is_array($text)){
            foreach($text as $t){
                foreach($qList as $qItem){
                    $qItem = trim($qItem);
                    if(strlen($qItem)>0){
                        $wheres[] = ['like',$t,$qItem];
                    }
                }
            }
            $query->andFilterWhere([$key=>$id]); // ->andFilterWhere($wheres) // 调整为支持逗号间隔批量查询
            count($wheres)>1 && $query->andFilterWhere($wheres);
        }else{
            foreach($qList as $qItem){
                $qItem = trim($qItem);
                if(strlen($qItem)>0){
                    $wheres[] = ['like',$text,$qItem];
                }
            }
            $query->andFilterWhere([$key=>$id]);  //->andFilterWhere(['like',$text,$q]) // 调整为支持逗号间隔批量查询
            count($wheres)>1 && $query->andFilterWhere($wheres);
        }
        
        
        if(!class_exists($className)){
            $text = is_array($text) ? reset($text) : $text;
            $query->select(["{$key} as id","{$text} as text"])
            ->from($className);
        }
        
        $this->col_where && $query->andFilterWhere($this->col_where);
        $this->col_sort && $query->orderBy($this->col_sort);
        
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSizeLimit' => [1, 5000],
            ],
        ]);
        
        $id && $dataProvider->setPagination(false);
        
        if(class_exists($className)){
            $result['items'] = [];
            $keySplit = explode('.',$key);
            $key = $keySplit ? end($keySplit) : $key;
            $text = ($this->col_v_text ? $this->col_v_text : (is_array($text) ? reset($text) : $text));
            $textSplit = explode('.',$text);
            $text = $textSplit ? end($textSplit) : $text;
            foreach($dataProvider->getModels() as $m){
                $result['items'][] = [
                    'id' => $m[$key],
                    'text' => $m[$text],
                ];
            }
        }else{
            $result['items'] = $dataProvider->getModels();
        }
        $result['total_page'] = $id ? 1 : $dataProvider->getPagination()->pageCount;
        
        return $result;
    }
}

