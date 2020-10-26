<?php
use yii\helpers\Html;
use webadmin\widgets\GridView;
use yii\helpers\Url;
use webadmin\modules\config\models\SysLdItem;

?>
<div class="row sys-ld-item-index">
	<div class="col-xs-12 col-md-12">
		<?php echo $this->render('_search', ['model' => $model]); ?>
		<div class="row">
			<div class="col-xs-12 col-md-6">
				<div class="widget flat radius-bordered">
            		<div class="widget-header bg-themeprimary">
            		    <span class="widget-caption"><?php echo Yii::t('config','数据字典')?></span>
            		    <div class="widget-buttons">
            				<a href="#" data-toggle="collapse" title="<?=Yii::t('common','最小化')?>"><i class="fa fa-minus"></i></a>
            				<a href="#" data-toggle="maximize" title="<?=Yii::t('common','最大化')?>"><i class="fa fa-expand"></i></a>
            		    </div>
            		</div>
            		<div class="widget-body checkForm">
            			<div class="row">
            				<div class="col-xs-12 col-md-12">
            					<div class="pull-right margin-bottom-10">
            						<?php /* <a class="btn btn-primary" href="<?php echo Url::to(['tree']);?>"><i class='fa fa-sitemap'></i> <?= Yii::t('common', '树型数据')?></a> */?>
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
                        	     'ident',
                        	     [
                        	         'attribute' => 'state',
                        	         'value' => 'v_state',
                        	     ],
        
                                [
                                	'class' => '\yii\grid\ActionColumn',
                                	'buttonOptions' => ['data-pjax'=>'0'],
                                    'template' => '{tree} {view} {update} {delete}',
                                    'buttons' => [
                                        'tree' => function ($url, $model) {
                                            return Html::a('<span class="fa fa-tree"></span>', $url, [
                                                'title' => Yii::t('config', '配置'),
                                                'id' => 'sys-ld-item-tree-btn',
                                                'data-pjax'=>'0',
                                            ]);
                        	             }
                    	            ],
                                ],
                            ],
                        ]); ?>
        
        		    </div>
        	    </div>
			</div>
			<div class="col-xs-12 col-md-6">
				<div class="widget flat radius-bordered">
            		<div class="widget-header bg-themeprimary">
            		    <span class="widget-caption"><?php echo Yii::t('config','字典选项')?></span>
            		    <div class="widget-buttons">
            				<a href="#" data-toggle="collapse" title="<?=Yii::t('common','最小化')?>"><i class="fa fa-minus"></i></a>
            				<a href="#" data-toggle="maximize" title="<?=Yii::t('common','最大化')?>"><i class="fa fa-expand"></i></a>
            		    </div>
            		</div>
            		<div class="widget-body checkForm" id="sys-ld-item-tree" style="max-height:850px;overflow:hidden;overflow-y:auto;">
            			<?php 
            			if(($id = Yii::$app->request->get('id'))){
            			    Yii::$app->session[Yii::$app->controller->id] = ['index','id'=>$id];
            			    echo $this->render('tree', [
            			        'model' => SysLdItem::findOne($id),
            			        'treeData' => \webadmin\modules\config\models\SysLdItem::treeData($id),
            			    ]);
            			}else{
            			    echo Yii::t('config','请先选择左侧需要配置的字典项');
            			}
            			?>
        		    </div>
        	    </div>
			</div>
		</div>
	</div>
</div>

<?php 
$this->registerJs("jQuery(document).off('click', '#sys-ld-item-tree-btn').on('click', '#sys-ld-item-tree-btn', function(){ $('#sys-ld-item-tree').load( $(this).attr('href') ); return false; });");
?>


