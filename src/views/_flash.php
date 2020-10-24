<?php 
$flashs = Yii::$app->session->getAllFlashes();
$c = array('error'=>'danger','warning'=>'warning','success'=>'success','info'=>'info');
?>
<?php if($flashs): // flash messages?>
	<?php foreach($flashs as $type=>$msgs):?>
		<div class="alert alert-<?php echo (isset($c[$type]) ? $c[$type] : $type)?> fade in">
		    <button class="close" data-dismiss="alert">×</button>
		    <?php echo is_array($msgs) ? implode('<br>',$msgs) : $msgs?>
		</div>
	<?php endforeach;?>
<?php endif;?>