<?php
	if(is_file(PDT_WORKING_DIR.'/scripts/'.$_POST['args']['script'].'.php')){
		unlink(PDT_WORKING_DIR.'/scripts/'.$_POST['args']['script'].'.php');
		echo json_encode(array('success' => 'true'));
	}else{
		echo json_encode(array('error' => 'Скрипт не найден'));
	}
?>