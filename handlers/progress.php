<?php
	$res = $PDT->getScriptProgress($_POST['args']['script']);
	if(!$res){
		echo json_encode(array('progress' => 1.0));
	}else{
		echo json_encode(array('progress' => $res));
	}
?>