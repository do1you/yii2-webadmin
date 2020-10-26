<?php

use yii\helpers\Inflector;
use yii\helpers\StringHelper;

$urlParams = $generator->generateUrlParams();
$nameAttribute = $generator->getNameAttribute();

echo "<?php\n";
?>
use yii\helpers\Html;
use webadmin\widgets\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

?>
<?= "<?php Pjax::begin(['timeout'=>5000]); ?>\n" ?>
<div class="row <?= Inflector::camel2id(StringHelper::basename($generator->modelClass)) ?>-index">
	<div class="col-xs-12 col-md-12">
<?= "		<?php " ?>echo $this->render('_search', ['model' => $model]); ?>
    	<div class="widget flat radius-bordered">
    		<div class="widget-header bg-themeprimary">
    		    <span class="widget-caption">&nbsp;</span>
    		    <div class="widget-buttons">
    				<a href="#" data-toggle="collapse" title="<?= "<?= " ?>Yii::t('common','最小化')?>"><i class="fa fa-minus"></i></a>
    				<a href="#" data-toggle="maximize" title="<?= "<?= " ?>Yii::t('common','最大化')?>"><i class="fa fa-expand"></i></a>
    		    </div>
    		</div>
    		<div class="widget-body checkForm">
    			<div class="row">
    				<div class="col-xs-12 col-md-12">
    					<div class="pull-right margin-bottom-10">
    						<?php echo "<?php /* "?><a class="btn btn-primary" href="<?php echo "<?php"?> echo Url::to(['tree']);?>"><i class='fa fa-sitemap'></i> <?= "<?= " ?>Yii::t('common','树型数据')?></a> */?>
    						<a class="btn btn-primary" href="<?php echo "<?php"?> echo Url::to(['create']);?>"><i class='fa fa-plus'></i> <?= "<?= " ?>Yii::t('common','添加')?></a>
    						<button class="btn btn-primary checkSubmit" reaction="<?php echo "<?php"?> echo Url::to(['delete']);?>" type="button"><i class='fa fa-trash-o'></i> <?= "<?= " ?>Yii::t('common','批量删除')?></button>
    						<?php echo "<?php"?> echo Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken());?>
    		    		</div>
    				</div>
    			</div>

            	<?= "<?= " ?>GridView::widget([
                    'dataProvider' => $dataProvider,
                    <?= "'filterModel' => \$model,\n                    'columns' => [\n"; ?>
                    	[
                    	    'class' => '\yii\grid\CheckboxColumn',
                    	    'name' => 'id',
                    	    'header' => '<label><input type="checkbox" name="id_all" class="checkActive select-on-check-all"><span class="text"></span></label>',
                    	    'content' => function($model, $key, $index){
                    	       return '<label><input type="checkbox" name="id[]" class="checkActive" value="'.$key.'"><span class="text"></span></label>';
            	            },
            	        ],
                    	['class' => '\yii\grid\SerialColumn'],

<?php
$count = 0;
if (($tableSchema = $generator->getTableSchema()) === false) {
    foreach ($generator->getColumnNames() as $name) {
        if (++$count < 6) {
            echo "                	     '" . $name . "',\n";
        } else {
            echo "                	     //'" . $name . "',\n";
        }
    }
} else {
    foreach ($tableSchema->columns as $column) {
        $format = $generator->generateColumnFormat($column);
        if (++$count < 6) {
            echo "                	     '" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        } else {
            echo "                	     //'" . $column->name . ($format === 'text' ? "" : ":" . $format) . "',\n";
        }
    }
}
?>

                        [
                        	'class' => '\yii\grid\ActionColumn',
                        	'buttonOptions' => ['data-pjax'=>'1'],
                        ],
                    ],
                ]); ?>

		    </div>
	    </div>
	</div>
</div>
<?= "<?php Pjax::end(); ?>\n" ?>


