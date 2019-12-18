<?php
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use webadmin\widgets\ActiveForm;

$group_id = $model->group_id;
$groupList = strlen($group_id) ? array($group_id=>$model->v_group_id) : $model->getV_group_id(false);
$script = '';
?>
<?php Pjax::begin(); ?>
<div class="row">
	<div class="col-xs-12">
		<div class="pull-right inline">
			<?php if(Yii::$app->controller->action->id=='config'):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['save-config','group_id'=>$group_id])?>"><i class='fa fa-cog'></i> 修改参数</a>
			<?php else:?>
				<a class="btn btn-primary" href="<?php echo Url::to(['config','group_id'=>$group_id])?>"><i class='fa fa-cog'></i> 查看参数</a>
			<?php endif;?>
			<?php if(strlen($group_id)<=0):?>
				<a class="btn btn-primary" href="<?php echo Url::to(['index'])?>"><i class="ace-icon glyphicon glyphicon-list bigger-110"></i> <?php echo Yii::t('common','参数管理')?></a>
			<?php endif;?>
		</div>
	</div>
</div>

<div class="form-title"></div>

<?php $form = ActiveForm::begin(); ?>
	<div class="row">
		<div class="col-xs-12">
		    <div class="widget flat radius-bordered">
			<div class="widget-header bg-themeprimary">
			    <span class="widget-caption"><?php echo (strlen($group_id) ? $model->v_group_id.'配置' : (Yii::$app->controller->action->id=='config' ? '查看配置' : '修改配置'))?></span>
			</div>
			<div class="widget-body">
			    <div class="widget-main ">
					<div class="tabbable">
					    <ul class="nav nav-tabs tabs-flat">
					    	<?php $num=0;foreach($groupList as $key=>$value):?>
					    		<?php if($num++ == 0):?>
					    			<li class="active"><a data-toggle="tab" href="#tab_<?php echo $key?>" aria-expanded="true"><?php echo $value?></a></li>
					    		<?php else:?>
					    			<li class=""><a data-toggle="tab" href="#tab_<?php echo $key?>" aria-expanded="false"><?php echo $value?></a></li>
					    		<?php endif;?>
							<?php endforeach;?>
					    </ul>
					    <div class="tab-content tabs-flat">
						    <?php $num=0;foreach($groupList as $key=>$value):?>
					    		<div id="tab_<?php echo $key?>" class="tab-pane<?php echo ($num++ == 0 ? ' active' : '')?>">
									<div class="row">
									
										<div class="col-sm-6 col-xs-12">
											<?php if(!empty($list[$key])):$num=0;foreach($list[$key] as $k=>$item):if($num++ % 2==0):?>
												<?php echo $this->render('_config',[
												    'item' => $item,
												    'k' => $k,
												    'form' => $form,
												])?>
											<?php endif;endforeach;endif;?>
										</div>

										<div class="col-sm-6 col-xs-12">
											<?php if(!empty($list[$key])):$num=0;foreach($list[$key] as $k=>$item):if($num++ % 2!=0):?>
												<?php echo $this->render('_config',[
												    'item' => $item,
												    'k' => $k,
												    'form' => $form,
												])?>
											<?php endif;endforeach;endif;?>
										</div>
									</div>
								</div>
							<?php endforeach;?>
							<?php if(Yii::$app->controller->action->id!='config'):?>
								<div class="row">
									<div class="col-sm-12 text-center">
									    <?= Html::submitButton(Yii::t('common', '保存'), ['class' => 'btn btn-primary shiny']) ?>
								    </div>
							    </div>
					    	<?php endif;?>
					    </div>
					</div>
			    </div>
			</div>
		    </div>
		</div>
	</div>
<?php ActiveForm::end(); ?>

<?php //if(Yii::$app->controller->action->id!='view') $this->renderPartial('//layouts/_validate'); ?>
<?php
/*
if(!empty($isMultiple)){
    $script = <<<eot
    $('.nav-tabs a').on('shown.bs.tab', function(e){
        $("select[multiple]").select2();
    });
    $("select[multiple]").select2();
eot;
}
if($script){
cs()
->registerScriptFile($this->assetPath.'js/select2/select2.js',CClientScript::POS_END)
->registerScript('formScript',$script,CClientScript::POS_READY)
;
}
*/
?>
<?php Pjax::end(); ?>