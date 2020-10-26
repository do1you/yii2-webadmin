<?php
use yii\helpers\Html;
use webadmin\widgets\GridView;
use yii\widgets\Pjax;
use yii\helpers\Url;

?>
<?php Pjax::begin(); ?>
<div class="row log-imei-index">
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
                    	/*[
                    	    'class' => '\yii\grid\CheckboxColumn',
                    	    'name' => 'id',
                    	    'header' => '<label><input type="checkbox" name="id_all" class="checkActive select-on-check-all"><span class="text"></span></label>',
                    	    'content' => function($model, $key, $index){
                    	       return '<label><input type="checkbox" name="id[]" class="checkActive" value="'.$key.'"><span class="text"></span></label>';
            	            },
            	        ],
                    	['class' => '\yii\grid\SerialColumn'],*/

                	     'id',
                	     [
                	         'attribute' => 'user_id',
                	         'value' => 'user.name',
                	     ],
                	     'platform',
                	     'imei',
                	     [
                	         'attribute' => 'create_time',
                	         'filter' => false,
                	     ],

                        [
                        	'class' => '\yii\grid\ActionColumn',
                        	'buttonOptions' => ['data-pjax'=>'1'],
                            'template' => '{view}',
                        ],
                    ],
                ]); ?>

		    </div>
	    </div>
	</div>
</div>
<?php Pjax::end(); ?>


