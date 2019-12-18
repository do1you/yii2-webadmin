<?php
if(!empty($_GET['type'])){
	echo '<div data-pui-asyncpage="" style="padding:20px;">传递过来的参数是：'.$_GET['type'];
}elseif(!empty($_GET['file'])){
	$data = file_get_contents("demo.html");
	preg_match_all("/<body.*?>(.*?)<\/body>/is",$data,$arr);
	print_r($arr[1][0]);exit;
}else{
	echo '<div data-pui-asyncpage="" style="padding:20px;">';
	echo "<pre>";

	echo "POST DATA：\r\n";
	print_r($_POST);

	echo "\r\nFILES DATA：\r\n";
	print_r($_FILES);
	echo "</pre>";
}

?>


	<a href="file.php?file=1">返回页面</a>
</div>