<?php if(!empty($navMenus) && is_array($navMenus)):?>
	<?php if(empty($isChild)):?>
		<ul class="nav sidebar-menu">
	<?php else:?>
		<ul class="submenu">
	<?php endif;?>
		<?php foreach($navMenus as $item):?>
			<?php if((defined('YII_DEBUG')&&YII_DEBUG) || $item['can_allowed']=='1' || in_array($item['id'],$userAuthoritys) || Yii::$app->user->id==1):?>
				<?php 
				// 链接地址
				if(substr($item['url'],0,4)=='http' || substr($item['url'],0,1)=='/'){
					$url = $item['url'];
				}elseif(substr($item['url'],0,1)=='#' || substr($item['url'],0,10)=='javascript'){
					$url = 'javascript:void(0)';
				}else{
				    $url = \yii\helpers\Url::toRoute('/'.trim($item['url'],'/'));	
				}
				$childs = (isset($item['childs']) ? $item['childs'] : []);
				foreach($childs as $k=>$v){
				    if(!in_array($v['flag'],['0'])){ // 0 为菜单
				        unset($childs[$k]);
				    }
				}
				$content = $this->render('@webadmin/views/_navmenu', array(
				                'navMenus'=>$childs,
								'userAuthoritys'=>$userAuthoritys,
								'currUrl'=>$currUrl,
								'isChild'=>true,
							),true);
				$active = ($item['url']==$currUrl || $item['url']==substr($currUrl,0,strripos($currUrl,'/'))) ? 'active' : '';
				$open = stripos($content,'active')>0 ? 'open' : '';
				
				?>
			    <li class="<?php echo $active;?> <?php echo $open?>">
					<a href="<?php echo $url?>" class="menu-dropdown">
					    <?php if($item['icon']):?><i class="menu-icon <?php echo $item['icon']?>"></i><?php endif;?>
					    <span class="menu-text"><?php echo $item['name']?></span>
					    <?php if(!empty($childs)):?><i class="menu-expand"></i><?php endif;?>
					</a>
					<?php echo $content; ?>
			    </li>
	    	<?php endif;?>
	    <?php endforeach;?>
	</ul>
<?php endif;?>