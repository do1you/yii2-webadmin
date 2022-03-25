<?php 

use yii\helpers\Url;

// 注册前端资源
\webadmin\WebAdminAsset::register($this);
?>
<?php $this->beginContent('@webadmin/views/html5.php'); ?>
    <div class="loading-container">
        <div class="loader"></div>
    </div>

    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-inner">
            <div class="navbar-container">
                <!-- Navbar Barnd -->
                <div class="navbar-header pull-left">
                    <a href="<?php echo Url::toRoute('/authority/user/index')?>" class="navbar-brand">
                        <small><img src="<?php echo Url::to('@assetUrl/images/logo.png')?>" alt="" /></small>
                    </a>
                </div>
                <!-- /Navbar Barnd -->
                <!-- Sidebar Collapse -->
                <div class="sidebar-collapse<?php echo Yii::$app->controller->is_open_nav ? '' : ' active'?>" id="sidebar-collapse">
                    <i class="collapse-icon fa fa-bars"></i>
                </div>
                <!-- /Sidebar Collapse -->
                <!-- Account Area and Settings --->
                <div class="navbar-header pull-right">
                    <div class="navbar-account">
                        <ul class="account-area">
                            <li>
                                <span style="line-height:45px;color:#dcdcdc;"><?php echo Yii::$app->name?></span>
                            </li>
                            <li>
                                <a class="login-area dropdown-toggle" data-toggle="dropdown">
                                    <div class="avatar">
                                        <img src="<?php echo Url::to('@assetUrl/img/avatars/adam-jansen.jpg');?>">
                                    </div>
                                    <section>
                                        <h2 style="min-width:60px;"><span class="profile"><span>&nbsp;<?php echo Yii::$app->user->identity['name']?></span></span></h2>
                                    </section>
                                </a>
                                <!--Login Area Dropdown-->
                                <ul class="pull-right dropdown-menu dropdown-arrow dropdown-login-area">
                                    <li class="username"><a>&nbsp;<?php echo Yii::$app->user->identity['name']?></a></li>
                                    <li class="email"><a><?php echo Yii::$app->user->identity['login_name']?></a></li>
                                    <!--Avatar Area-->
                                    <li>
                                        <div class="avatar-area">
                                            <img src="<?php echo Url::to('@assetUrl/img/avatars/adam-jansen.jpg');?>" class="avatar">
                                            <span class="caption"><?php echo Yii::t('common','如果想要，就一定行')?></span>
                                        </div>
                                    </li>
                                    <!--Avatar Area-->
                                    <li class="edit">
                                        <a href="<?php echo Url::toRoute('/authority/user/info')?>" class="pull-left"><?php echo Yii::t('authority', '修改资料')?></a>
                                        <a href="<?php echo Url::toRoute('/authority/user/password')?>" class="pull-right"><?php echo Yii::t('authority', "修改密码")?></a>
                                    </li>
                                    <!--Theme Selector Area-->
                                    <li class="theme-area">
                                        <ul class="colorpicker" id="skin-changer">
                                            <li><a class="colorpick-btn" href="#" style="background-color:#5DB2FF;" rel="<?php echo Url::to('@assetUrl/css/skins/blue.min.css');?>"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#2dc3e8;" rel="<?php echo Url::to('@assetUrl/css/skins/azure.min.css');?>"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#03B3B2;" rel="<?php echo Url::to('@assetUrl/css/skins/teal.min.css');?>"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#53a93f;" rel="<?php echo Url::to('@assetUrl/css/skins/green.min.css');?>"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#FF8F32;" rel="<?php echo Url::to('@assetUrl/css/skins/orange.min.css');?>"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#cc324b;" rel="<?php echo Url::to('@assetUrl/css/skins/pink.min.css');?>"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#AC193D;" rel="<?php echo Url::to('@assetUrl/css/skins/darkred.min.css');?>"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#8C0095;" rel="<?php echo Url::to('@assetUrl/css/skins/purple.min.css');?>"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#0072C6;" rel="<?php echo Url::to('@assetUrl/css/skins/darkblue.min.css');?>"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#585858;" rel="<?php echo Url::to('@assetUrl/css/skins/gray.min.css');?>"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#474544;" rel="<?php echo Url::to('@assetUrl/css/skins/black.min.css');?>"></a></li>
                                            <li><a class="colorpick-btn" href="#" style="background-color:#001940;" rel="<?php echo Url::to('@assetUrl/css/skins/deepblue.min.css');?>"></a></li>
                                        </ul>
                                    </li>
                                    <!--/Theme Selector Area-->
                                    <li class="dropdown-footer"><a href="<?php echo Url::toRoute('/authority/user/logout')?>">退出登录</a></li>
                                </ul>
                                <!--/Login Area Dropdown-->
                            </li>
                            <!-- /Account Area -->
                            <!-- Settings -->
                        </ul><div class="setting">
                            <a id="btn-setting" title="Setting" href="#">
                                <i class="icon glyphicon glyphicon-cog"></i>
                            </a>
                        </div><div class="setting-container">
                            <label>
                                <input type="checkbox" id="checkbox_fixednavbar">
                                <span class="text"><?php echo Yii::t('authority', '固定导航')?></span>
                            </label>
                            <label>
                                <input type="checkbox" id="checkbox_fixedsidebar">
                                <span class="text"><?php echo Yii::t('authority', '固定侧边')?></span>
                            </label>
                            <label>
                                <input type="checkbox" id="checkbox_fixedheader">
                                <span class="text"><?php echo Yii::t('authority', '固定头部')?></span>
                            </label>
                        </div>
                        <!-- Settings -->
                    </div>
                </div>
                <!-- /Account Area and Settings -->
            </div>
        </div>
    </div>
    <!-- /Navbar -->
    <!-- Main Container -->
    <div class="main-container container-fluid">
        <!-- Page Container -->
        <div class="page-container">
            <!-- Page Sidebar -->
            <div class="page-sidebar<?php echo Yii::$app->controller->is_open_nav ? '' : ' menu-compact'?>" id="sidebar">
                <?php
				// 导航菜单
                $allMenus = \webadmin\modules\authority\models\AuthAuthority::treeMenu();
                $userAuthoritys = Yii::$app->user->identity ? Yii::$app->user->identity->getCache('getAuthorithIds',[Yii::$app->user->id]) : [];
				if(Yii::$app->controller->currUrl === null) 
				    Yii::$app->controller->currUrl = (!(Yii::$app->controller->module instanceof \yii\base\Application) ? Yii::$app->controller->module->id.'/' : '') . Yii::$app->controller->id.'/'.(in_array(Yii::$app->controller->action->id,['create','update','view','index','tree']) ? 'index' : Yii::$app->controller->action->id);
				echo $this->render('@webadmin/views/_navmenu', ['navMenus'=>$allMenus,'userAuthoritys'=>$userAuthoritys,'currUrl'=>Yii::$app->controller->currUrl]);
				?>
            </div>
            <!-- /Page Sidebar -->
            <!-- Page Content -->
            <div class="page-content">
                <!-- Page Header -->
                <div class="page-header position-relative">
                    <div class="header-title">
                        <h1>
	                    	<ul class="breadcrumb" style="margin-left:0;">
		                        <li><i class="fa fa-home"></i><a href="<?php echo Url::toRoute('/authority/user/index')?>">首页</a></li>
		                        <?php if(property_exists(Yii::$app->controller,'currNav') && Yii::$app->controller->currNav): Yii::$app->controller->currNav = is_array(Yii::$app->controller->currNav) ? Yii::$app->controller->currNav : [Yii::$app->controller->currNav];?>
		                        	<?php $maxKey = count(Yii::$app->controller->currNav);foreach(Yii::$app->controller->currNav as $k=>$item):?>
		                        		<?php if(is_string($item)):?>
		                        			<li<?php echo ($k+1==$maxKey ? ' class="active"' : '');?>><?php echo $item?></li>
		                        		<?php else:?>
    		                        		<?php if(!empty($item['url'])):?>
    		                        			<li<?php echo ($k+1==$maxKey ? ' class="active"' : '');?>><a href="<?php echo $item['url']?>"><?php echo $item['title']?></a></li>
    		                        		<?php else:?>
    		                        			<li<?php echo ($k+1==$maxKey ? ' class="active"' : '');?>><?php echo $item['title']?></li>
    		                        		<?php endif;?>
		                        		<?php endif;?>
		                        	<?php endforeach;?>
		                        <?php endif;?>
		                    </ul>
                        </h1>
                    </div>
                    <div class="header-buttons">
                        <a class="fullscreen" id="fullscreen-toggler" href="#" title="最大化"><i class="glyphicon glyphicon-fullscreen"></i></a>
                        <a class="sidebar-toggler" href="#" title="隐藏菜单"><i class="fa fa-arrows-h"></i></a>
                        <a class="refresh" id="refresh-toggler" href="<?php echo Url::toRoute('/authority/user/clearcache')?>" title="刷新缓存"><i class="glyphicon glyphicon-refresh"></i></a>
                    </div>
                </div>
                <!-- /Page Header -->
                <!-- Page Body -->
                <div class="page-body">
                	<?php echo $this->render('@webadmin/views/_flash'); ?>
                    <?php echo $content?>
                </div>
                <!-- /Page Body -->
            </div>
            <!-- /Page Content -->
        </div>
        <!-- /Page Container -->
        <!-- Main Container -->
    </div>
    <div class="hide" id="hiddenDiv"></div>
<?php $this->endContent(); ?>