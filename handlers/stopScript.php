<?php
	$filename = PDT_WORKING_DIR.'/data/scripts/status/'.$_POST['args']['script'].'.var';
	if(is_file($filename) && @unlink($filename)){
		echo json_encode(array(1));
	}else{
		echo json_encode(array(0));
	}
?>