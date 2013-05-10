<?php
	if(!$_POST['args']['script']){
		$data['content'] = '<?php
// Тело скрипта	
?>';
		$data['script_name'] = 'Новый_скрипт';
	}else{
		$data['content'] = file_get_contents(PDT_WORKING_DIR.'/scripts/'.$_POST['args']['script'].'.php');
		$data['script_name'] = $_POST['args']['script'];
	}
	echo json_encode($data);
	// Comment
?>