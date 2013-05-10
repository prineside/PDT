<?php
class Listener extends PseudoDaemon{
	public $timer;
	public $timeout = 25;
	
	function __construct(){
		$this->timer = time();
	}
	
	function Listen(){
		$scripts = $this->getScriptList();	// Массив скриптов и хэшей их консолей (текущий)

		foreach($scripts as $s){
			if(is_file(PDT_WORKING_DIR.'/data/listener/'.$s['script'].'.var')){	// Хэш с последним изменением
				clearstatcache();
				$s_last = file_get_contents(PDT_WORKING_DIR.'/data/listener/'.$s['script'].'.var');
			}else{
				$s_last = '';
			}
			
			if($s_last!=$s['stamp']){	// Хэши не совпали, что-то поменялось в консоли
				$ret[] = $s['script'];
				file_put_contents(PDT_WORKING_DIR.'/data/listener/'.$s['script'].'.var', $s['stamp']);
				clearstatcache();
			}
		}
		
		if(is_array($ret)){
			echo json_encode($ret);
			die();
		}else{
			if((time() - $this->timer)<$this->timeout){
				usleep(50000);	// 50 ms
				$this->Listen();
			}else{	// timeout
				echo json_encode(array('timeout'=>1));
				die();
			}
		}
	}
}

$PDT = new Listener();
$PDT->Listen();
?>