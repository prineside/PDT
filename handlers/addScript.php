<?php
	$from = array("`","~","!","@","#","$","%","^","&","*","(",")","-","=","+","\\","|","?","/",",",";",":","№","'","\"",".");
	$_POST['args']['script'] = str_replace($from, '_', $_POST['args']['script']);
	$_POST['args']['script'] = preg_replace('/\_+/','_', $_POST['args']['script']);
	if($_POST['args']['script']!='_'&&$_POST['args']['script']){
		$src = fopen(PDT_WORKING_DIR.'/scripts/'.$_POST['args']['script'].'.php','w');
		fwrite($src, '<?php
// Содержимое скрипта
?>');
		echo json_encode(array('script'=>$_POST['args']['script']));
	}else{
		echo json_encode(array('error'=>'В названии скрипта не должно быть спецсимволов, имя не может быть пустой строкой'));
	}
?>