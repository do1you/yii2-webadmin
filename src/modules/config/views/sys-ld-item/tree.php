<?php

use yii\helpers\Url;

?>
<?php if(!empty($showLayout)): // 树型数据节点?>
	<ol class="dd-list">
		<?php foreach($treeData as $item):?>
			<?php			
			$content = !empty($item['children']) ? $this->render('tree', array(
				'treeData'=>$item['children'],
			    'showLayout'=>true,
			),true) : "";
			?>
            <li class="dd-item dd-nodrag dd2-item" data-id="<?php echo $item['id']?>">
                <div class="dd2-handle" >
                    <i class="normal-icon <?php echo (!empty($item['icon']) ? $item['icon'] : 'fa fa-bookmark')?>"></i>
                    <i class="drag-icon fa fa-arrows-alt "></i>
                </div>
                <div class="dd2-content">
                	<span class="pull-left"><?php echo $item['name']?></span>
                    <span class="pull-right">
                        <a class="btn btn-primary btn-xs" href="<?php echo Url::to(['create','id'=>$item['id']])?>"><i class='fa fa-plus'></i> <?= Yii::t('common', '添加')?></a>&nbsp;
                        <a class="btn btn-primary btn-xs edit" href="<?php echo Url::to(['update','id'=>$item['id']])?>"><i class='fa fa-edit'></i> 编辑</a>&nbsp;
                        <a class="btn btn-primary btn-xs delete" href="<?php echo Url::to(['delete','id'=>$item['id']])?>"><i class='fa fa-trash-o'></i> 删除</a>
                    </span>
                </div>
        		<?php echo $content; ?>
        	</li>
	    <?php endforeach;?>
	</ol>
<?php return;endif;?>

<div class="row sys-ld-item-tree">
	<div class="col-xs-12 col-md-12">
		<div class="row">
			<div class="col-xs-12 col-md-12">
				<div class="pull-left">
					<h5 class="row-title before-themeprimary no-margin"><i class="fa fa-medium"></i> <?php echo ($model ? $model['name'] : '')?></h5>
				</div>
				<div class="pull-right margin-bottom-10">
					<a class="btn btn-primary" href="<?php echo Url::to(['create','id'=>Yii::$app->request->get('id')]);?>"><i class='fa fa-plus'></i> <?= Yii::t('common', '添加')?></a>
	    		</div>
			</div>
		</div>

    	<div class="dd dd-draghandle bordered widthAuto dd-button">
            <?php 
            if($treeData){
                echo $this->render('tree', array(
                    'treeData'=>$treeData,
                    'showLayout'=>true,
                ),true);
                $this->registerJsFile('@assetUrl/js/nestable/jquery.nestable.min.js',['depends' => \webadmin\WebAdminAsset::className()]);
                $this->registerJs("$('.dd').nestable();");
            }else{
                echo "<hr>暂无数据";
            }
            ?>
        </div>
	</div>
</div>

