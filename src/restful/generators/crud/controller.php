<?php
echo "<"."?php\n";
use yii\helpers\StringHelper;
?>
/**
 * 模型对象 <?= $generator->modelClass?> 的操作方法
 */
namespace <?= StringHelper::dirname(ltrim($generator->controllerClass, '\\')) ?>;

use Yii;

class <?= StringHelper::basename($generator->controllerClass)?> extends <?= $generator->baseControllerClass . "\n" ?>
{
    /**
     * 定义基于控制器进行操作的模型
     */
    public $modelClass = '<?= $generator->modelClass?>';
    
    /**
     * 定义默认的控制的操作方法
     */
    public function actions()
    {
        return [
            // 列表
            'index' => [
                'class' => '\webadmin\restful\IndexAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
                'dataFilter' => [
                    'class' => 'yii\data\ActiveDataFilter',
                    'searchModel' => [
                        'class' => $this->modelClass,
                    ],
                ],
            ],
            // 查看详情
            'view' => [
                'class' => '\webadmin\restful\ViewAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            // 添加
            'create' => [
                'class' => '\webadmin\restful\CreateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            // 修改
            'update' => [
                'class' => '\webadmin\restful\UpdateAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            // 删除
            'delete' => [
                'class' => '\webadmin\restful\DeleteAction',
                'modelClass' => $this->modelClass,
                'checkAccess' => [$this, 'checkAccess'],
            ],
            // 树形数据
            /*
            'tree' => [
				'class' => '\webadmin\restful\TreeAction',
				'modelClass' => $this->modelClass,
				'checkAccess' => [$this, 'checkAccess'],
				//'colOrder' => 'paixu desc',
				//'colWhere' => 'state=0',
			],
            */
        ];
    }
}
