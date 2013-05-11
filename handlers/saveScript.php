<?php
	if($_POST['args']['script'] && $_POST['args']['content']){
		$src = fopen(PDT_WORKING_DIR.'/scripts/'.$_POST['args']['script'].'.php', 'w');
		fwrite($src, stripslashes($_POST['args']['content']));
		fclose($src);
		echo json_encode(array('success' => 'true'));
	}else{
		echo json_encode(array('error' => 'Недостаточно данных для сохранения'));
	}
?>