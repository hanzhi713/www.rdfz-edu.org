<?php
if (isset($_GET['confirm'])){
	$path = $_GET['dir'];
	$hostdir = dirname($path);
	$filenames = scandir($path);
	include_once('en-US/phps/imageResize.php');
	foreach ($filenames as $file){
		$ext = explode('.', $file)[1];
		if ($ext == 'jpg' || $ext == 'png'){
			echo $file, '<br>';
			imageResize("$path/$file","$path/$file.mini", 200);
		}
	}
}
?>