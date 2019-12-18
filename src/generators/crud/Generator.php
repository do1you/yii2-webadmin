<?php
/**
 * 继承系统类库增删改查生成器
 */

namespace webadmin\generators\crud;

use Yii;
use yii\db\ActiveRecord;
use yii\db\BaseActiveRecord;
use yii\db\Schema;
use yii\gii\CodeFile;
use yii\helpers\Inflector;
use yii\helpers\VarDumper;
use yii\web\Controller;

class Generator extends \yii\gii\generators\crud\Generator
{
    public $modelClass;
    public $controllerClass;
    public $viewPath;
    public $baseControllerClass = '\webadmin\BController';
    public $indexWidgetType = 'grid';
    public $searchModelClass = '';
    public $enablePjax = true;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return '增删改查生成器';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return '通过模型对象，快速创建增删改查的控制器及模板方法.';
    }
    
    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return array_merge(parent::attributeLabels(), [
            'modelClass' => '模型对象',
            'controllerClass' => '控制器方法',
            'viewPath' => '视图路径',
            'baseControllerClass' => '控制器基类',
            'enableI18N' => '开启国际化',
            'messageCategory' => '国际化语言包',
        ]);
    }
    
    /**
     * {@inheritdoc}
     */
    public function generate()
    {
        $controllerFile = Yii::getAlias('@' . str_replace('\\', '/', ltrim($this->controllerClass, '\\')) . '.php');
        
        $files = [
            new CodeFile($controllerFile, $this->render('controller.php')),
        ];
        
        $viewPath = $this->getViewPath();
        $templatePath = $this->getTemplatePath() . '/views';
        foreach (scandir($templatePath) as $file) {
            if (is_file($templatePath . '/' . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
                $files[] = new CodeFile("$viewPath/$file", $this->render("views/$file"));
            }
        }
        
        return $files;
    }
    
    /**
     * {@inheritdoc}
     */
    public function generateActiveSearchField($attribute)
    {
        $tableSchema = $this->getTableSchema();
        if ($tableSchema === false) {
            return "\$form->field(\$model, '$attribute')->searchInput()";
        }
        
        $column = $tableSchema->columns[$attribute];
        if ($column->phpType === 'boolean') {
            return "\$form->field(\$model, '$attribute')->searchInput()->checkbox()";
        }
        
        return "\$form->field(\$model, '$attribute')->searchInput()";
    }
}
