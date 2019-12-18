<?php
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;
use webadmin\widgets\ActiveForm;
$model->parent_id = is_array($model->parent_id) ? implode(",",$model->parent_id) : $model->parent_id;
?>
<?php Pjax::begin(); ?>
<div class="row auth-authority-index">
	<div class="col-xs-12 col-md-12">
		<?php echo $this->render('_search', ['model' => $model]); ?>
    	<div class="widget flat radius-bordered">
    		<div class="widget-header bg-themeprimary">
    		    <span class="widget-caption">&nbsp;</span>
    		    <div class="widget-buttons">
    				<a href="#" data-toggle="collapse" title="<?=Yii::t('common','最小化')?>"><i class="fa fa-minus"></i></a>
    				<a href="#" data-toggle="maximize" title="<?=Yii::t('common','最大化')?>"><i class="fa fa-expand"></i></a>
    		    </div>
    		</div>
    		<div class="widget-body checkForm">
    			<div class="row">
    				<div class="col-xs-12 col-md-12">
    					<div class="pull-right margin-bottom-10">
    						<a class="btn btn-primary" href="<?php echo Url::to(['tree']);?>"><i class='fa fa-sitemap'></i> <?= Yii::t('common', '树型数据')?></a>
    						<a class="btn btn-primary" href="<?php echo Url::to(['create']);?>"><i class='fa fa-plus'></i> <?= Yii::t('common', '添加')?></a>
    						<button class="btn btn-primary checkSubmit" reaction="<?php echo Url::to(['delete']);?>" type="button"><i class='fa fa-trash-o'></i> <?= Yii::t('common', '批量删除')?></button>
    						<?php echo Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->getCsrfToken());?>
    		    		</div>
    				</div>
    			</div>

            	<?= GridView::widget([
                    'dataProvider' => $dataProvider,
                    'pager' => [
                    	'firstPageLabel' => Yii::t('common','首页'),
                    	'prevPageLabel' => Yii::t('common','上一页'),
                    	'nextPageLabel' => Yii::t('common','下一页'),
                    	'lastPageLabel' => Yii::t('common','尾页'),
                    ],
                    'layout' => "{items}\n<div class='row margin-top-10'><div class='col-xs-12 text-center'><div class='pull-left'>{summary}</div>{pager}</div></div>",
                    'filterModel' => $model,
                    'columns' => [
                    	[
                    	    'class' => '\yii\grid\CheckboxColumn',
                    	    'name' => 'id',
                    	    'header' => '<label><input type="checkbox" name="id_all" class="checkActive select-on-check-all"><span class="text"></span></label>',
                    	    'content' => function($model, $key, $index){
                    	       return '<label><input type="checkbox" name="id[]" class="checkActive" value="'.$key.'"><span class="text"></span></label>';
            	            },
            	        ],
                    	['class' => '\yii\grid\SerialColumn'],

                	     'id',
                	     'name',
                	     'url',
                	     [
                	         'attribute' => 'parent_id',
                	         'value' => 'v_parent',
                	     ],
                	     [
                	         'attribute' => 'flag',
                	         'value' => 'v_flag',
                	     ],
                	     [
                	         'attribute' => 'can_allowed',
                	         'value' => 'v_can_allowed',
                	     ],
                	     'paixu',

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
<?php Pjax::end(); ?>


