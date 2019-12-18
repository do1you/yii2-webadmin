<?php
$page = empty($_GET['page']) ? 1 : intval($_GET['page']);
echo '<ul style="font-size:14px;margin:0;padding:0;">';
for($i=1;$i<=20;$i++){
	echo '<li>第'.$page.'页：'.$i.' '.date('Y-m-d H:i:s').'</li>';
}
echo '</ul>';
if($page<20){
	$page++;
	echo '<a href="file.php?page='.$page.'" class="next" style="display:none;">下一页</a>';
}