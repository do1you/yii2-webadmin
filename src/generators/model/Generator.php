<?php
/**
 * 继承系统类库模型生成器
 */

namespace webadmin\generators\model;

use Yii;
use yii\base\NotSupportedException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Connection;
use yii\db\Schema;
use yii\db\TableSchema;
use yii\gii\CodeFile;
use yii\helpers\Inflector;

class Generator extends \yii\gii\generators\model\Generator
{
    const RELATIONS_NONE = 'none';
    const RELATIONS_ALL = 'all';
    const RELATIONS_ALL_INVERSE = 'all-inverse';

    public $db = 'db';
    public $ns = 'app\models';
    public $tableName;
    public $modelClass;
    public $baseClass = '\webadmin\ModelCAR';
    public $generateRelations = self::RELATIONS_ALL;
    public $generateRelationsFromCurrentSchema = true;
    public $generateLabelsFromComments = true;
    public $useTablePrefix = false;
    public $standardizeCapitals = false;
    public $singularize = false;
    public $useSchemaName = true;
    public $generateQuery = false;
    public $enableI18N = true;


    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return '模型生成器';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return '通过数据库表信息快速生成模型.';
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'ns' => '命名空间',
            'db' => '数据库连接',
            'tableName' => '表名称',
            'standardizeCapitals' => '标准化大写',
            'singularize' => '驼峰化',
            'modelClass' => '模型类名称',
            'baseClass' => '继承基类',
            'generateRelations' => '生成关系',
            'generateRelationsFromCurrentSchema' => '所有关系通过当前表结构生成',
            'generateLabelsFromComments' => '标签采用字段描述',
            'useSchemaName' => '使用表结构名称',
            'enableI18N' => '开启国际化',
            'messageCategory' => '国际化语言包',
            'useTablePrefix' => '表前缀',
        ]);
    }

}
