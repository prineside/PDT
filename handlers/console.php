<?php
	$res = $PDT->getScriptConsole($_POST['args']['script']);
	if(!$res){
		PDT_HandleError('Скрипт <b>'.$_POST['args']['script'].'</b> не имеет файла консоли');
	}else{
		echo my_json_encode($res);
	}
?>