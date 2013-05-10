<?php
	if(is_file(PDT_WORKING_DIR.'/scripts/'.$_POST['script'].'.php')){
		$PDT->script_name = $_POST['script'];
		if($PDT->scriptIsRunning($_POST['script'])){
			$PDT->display('Скрипт уже запущен');
			die();
		}
		
		@unlink(PDT_WORKING_DIR.'/data/scripts/output/'.$_POST['script'].'.var');

		ob_start();
			
		set_time_limit(0);
		ignore_user_abort();

		$PDT->display('Скрипт начал работу', 'startup');
		$PDT->setStatus(1);
		register_shutdown_function(array(&$PDT,"truncate"));
		// register_shutdown_function(array(&$PDT,"shutdown_handler"));
		include(PDT_WORKING_DIR.'/scripts/'.$_POST['script'].'.php');
			
		if($ogc = ob_get_contents()){
			PDT_HandleError($ogc);
		}
		ob_end_clean();
	}else{
		PDT_HandleError('Script <b>'.$_POST['script'].'</b> not found');
	}
?>