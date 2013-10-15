<?php
class PseudoDaemon {

	public $script_name;
	public $ram = array();
	public $last_action_timestamp;	// float, seconds
	public $max_action_delay = 1.5;	// float, seconds
	public $max_dead_script_action_delay = 10;	// float, seconds
	public $console_max_lines = 100;	
	
	function __construct(){
		$im_dirs = array(
			'/data',
			'/data/listener',
			'/data/ram',
			'/data/scripts',
			'/data/scripts/input',
			'/data/scripts/output',
			'/data/scripts/status',
			'/data/scripts/usage',
			'/scripts'
		);
		foreach($im_dirs as $dir){
			if(!is_dir(PDT_WORKING_DIR.$dir)){
				mkdir(PDT_WORKING_DIR.$dir, 0777);
			}
		}
	}
	
	function __destruct(){
		$error = error_get_last();
		ErrorHandler($error['type'], $error['message'], $error['file'], $error['line']);
	}
	
	function getScriptList(){
		$src = opendir(PDT_WORKING_DIR.'/scripts');
		while($script = readdir($src)){
			if(is_file(PDT_WORKING_DIR.'/scripts/'.$script)){
				$script = substr($script,0,-4);
				clearstatcache();
				$size = 0;
				if(is_file(PDT_WORKING_DIR.'/data/scripts/output/'.$script.'.var')){
					$cons_stmp = substr(md5(filemtime(PDT_WORKING_DIR.'/data/scripts/output/'.$script.'.var').filesize(PDT_WORKING_DIR.'/data/scripts/output/'.$script.'.var')),0,4);
				}else{
					$cons_stmp = 0;
				}
				$status = $this->scriptIsRunning($script);
				if(is_dir(PDT_WORKING_DIR.'/data/ram/'.$script)){
					$ram = opendir(PDT_WORKING_DIR.'/data/ram/'.$script);
					while($ram_var = readdir($ram)){
						if(is_file(PDT_WORKING_DIR.'/data/ram/'.$script.'/'.$ram_var)){
							$size += filesize(PDT_WORKING_DIR.'/data/ram/'.$script.'/'.$ram_var);
						}
					}
					closedir($ram);
				}
				$ret[] = array(
					'script' => $script,
					'status' => $status,
					'stamp' => $cons_stmp,
					'size' => $size,
					'ramsize' => $this->getScriptRAM($script)
				);
			}
		}
		return $ret;
	}
	
	function getScriptRAM($srcipt){
		$is_file = is_file(PDT_WORKING_DIR.'/data/scripts/usage/'.$srcipt);
		if(!$is_file){
			usleep(10);
			$is_file = is_file(PDT_WORKING_DIR.'/data/scripts/usage/'.$srcipt);
		}
		if($is_file){
			return file_get_contents(PDT_WORKING_DIR.'/data/scripts/usage/'.$srcipt);
		}else{
			return 0;
		}
	}
	
	function getScriptConsole($script){
		clearstatcache();
		$is_file = is_file(PDT_WORKING_DIR.'/data/scripts/output/'.($script).'.var');
		if(!$is_file){
			usleep(5);
			$is_file = is_file(PDT_WORKING_DIR.'/data/scripts/output/'.($script).'.var');
		}
		if($is_file){
			$arr = @file(PDT_WORKING_DIR.'/data/scripts/output/'.($script).'.var');
			if($arr === false){
				usleep(5);
				$arr = @file(PDT_WORKING_DIR.'/data/scripts/output/'.($script).'.var');
			}
			foreach((array)$arr as $line){
				$temp = explode('|', $line);
				$ret[] = array(
					'time'=>date('H:i:s', trim($temp[0])),
					'date'=>date('d.m.Y', trim($temp[0])),
					'type'=>trim($temp[1]),
					'message'=>trim($temp[2])
				);
			}
			return $ret;
		}else{
			return false;
		}
	}
	
	function getScriptProgress($script){
		if(is_file(PDT_WORKING_DIR.'/data/scripts/progress/'.($script).'.var')){
			return file_get_contents(PDT_WORKING_DIR.'/data/scripts/progress/'.($script).'.var');
		}else{
			return false;
		}
	}
	
	function running(){
		clearstatcache();
		if(is_file(PDT_WORKING_DIR.'/data/scripts/status/'.($this->script_name).'.var')){
			$this->action();
			return true;
		}else{
			return false;
		}
	}
	
	function wait($ms){
		$this->action();
		$this->setStatus(2);
		usleep($ms*1000);
		if($this->running()){
			$this->setStatus(1);
			$this->action();
		}
	}
	
	function scriptIsRunning($script){
		clearstatcache();
		if(is_file(PDT_WORKING_DIR.'/data/scripts/status/'.$script.'.var')){
			if((time()-filemtime(PDT_WORKING_DIR.'/data/scripts/status/'.$script.'.var')) < ($this->max_dead_script_action_delay)){
				// 0 -not running
				// 1 -running
				// 2 -sleeping (waiting)
				return trim(file_get_contents(PDT_WORKING_DIR.'/data/scripts/status/'.$script.'.var'));
			}else{
				return 0;
			}
		}else{
			return 0;
		}
	}
	
	function action(){
		if(($this->max_action_delay)<=(microtime(1)-($this->last_action_timestamp))){
			touch(PDT_WORKING_DIR.'/data/scripts/status/'.($this->script_name).'.var');
			$this->last_action_timestamp = microtime(1);
			file_put_contents(PDT_WORKING_DIR.'/data/scripts/usage/'.($this->script_name), memory_get_usage());
		}
	}
	
	function clearMemory(){
		if(is_dir(PDT_WORKING_DIR.'/data/ram/'.($this->script_name))){
			$src = opendir(PDT_WORKING_DIR.'/data/ram/'.($this->script_name));
			while($obj = readdir($src)){
				if(is_file(PDT_WORKING_DIR.'/data/ram/'.($this->script_name).'/'.$obj)){
					unlink(PDT_WORKING_DIR.'/data/ram/'.($this->script_name).'/'.$obj);
				}
			}
		}
	}
	
	function setStatus($status){
		$src = fopen(PDT_WORKING_DIR.'/data/scripts/status/'.($this->script_name).'.var', 'w');
		fwrite($src, $status);
		fclose($src);
	}
	
	function truncate($bool=false){
		if(!$bool){
			$this->display('Скрипт завершил работу','shutdown');
		}
		#$this->clearMemory();
		@unlink(PDT_WORKING_DIR.'/data/scripts/status/'.($this->script_name).'.var');
		die();
	}
	
	function shutdown_error(){
		$error = error_get_last();
        if(isset($error['ERRNO'])){
			$src = fopen('pdt_error.log','a');
			fwrite($src, json_encode($error));
		}
		$this->truncate();
	}
	
	function display($msg, $type = 'text'){
		clearstatcache();
		$msg = str_replace("\n", "", str_replace("\r\n","", $msg));
		if(is_file(PDT_WORKING_DIR.'/data/scripts/output/'.($this->script_name).'.var')){
			$f_arr = file(PDT_WORKING_DIR.'/data/scripts/output/'.($this->script_name).'.var');
			$size = sizeof($f_arr);
		}else{
			$size = 0;
		}
		if($size >= $this->console_max_lines && $this->console_max_lines){
			file_put_contents(PDT_WORKING_DIR.'/data/scripts/output/'.($this->script_name).'.var', '');
			$src = fopen(PDT_WORKING_DIR.'/data/scripts/output/'.($this->script_name).'.var', 'a');
			
			for($i=1; $i<($this->console_max_lines); $i++){
				fwrite($src, trim($f_arr[$i])."\r\n");
			}
			fwrite($src, time().'|'.$type.'|'.$msg."\r\n");
			fclose($src);
		}else{
			$src = fopen(PDT_WORKING_DIR.'/data/scripts/output/'.($this->script_name).'.var', 'a');
			fwrite($src, time().'|'.$type.'|'.$msg."\r\n");
			fclose($src);
		}
		//$this->action();
	}
	
	function progress($coefficient){
		file_put_contents(PDT_WORKING_DIR.'/data/scripts/progress/'.($this->script_name).'.var', $coefficient);
	}
	
	function write($var, $val){
		if(strpos($var, '.')===false){
			$script = $this->script_name;
		}else{
			$tmp = explode('.',$var);
			$script = $tmp[0];
			$var = $tmp[1];
		}
		if(!is_dir(PDT_WORKING_DIR.'/data/ram/'.$script)){
			mkdir(PDT_WORKING_DIR.'/data/ram/'.$script);
		}
		$val = serialize($val);
		$src = fopen(PDT_WORKING_DIR.'/data/ram/'.$script.'/'.$var.'.var', 'w');
		fwrite($src, $val);
		fclose($src);
	}
	
	function read($var){
		if(strpos($var, '.')===false){
			$script = $this->script_name;
		}else{
			$tmp = explode('.',$var);
			$script = $tmp[0];
			$var = $tmp[1];
		}
		clearstatcache();
		if(is_file(PDT_WORKING_DIR.'/data/ram/'.$script.'/'.$var.'.var')){
			return unserialize(file_get_contents(PDT_WORKING_DIR.'/data/ram/'.$script.'/'.$var.'.var'));
		}else{
			return false;
		}
	}
	
	function input(){
		clearstatcache();
		if(is_file(PDT_WORKING_DIR.'/data/scripts/input/'.$this->script_name.'.var')){
			$inp = file_get_contents(PDT_WORKING_DIR.'/data/scripts/input/'.$this->script_name.'.var');
			unlink(PDT_WORKING_DIR.'/data/scripts/input/'.$this->script_name.'.var');
			return $inp;
		}else{
			return false;
		}
	}
	
	function getVariableList($script){
		clearstatcache();
		if(is_dir(PDT_WORKING_DIR.'/data/ram/'.$script)){
			$src = opendir(PDT_WORKING_DIR.'/data/ram/'.$script);
			while($obj = readdir($src)){
				if(is_file(PDT_WORKING_DIR.'/data/ram/'.$script.'/'.$obj)){
					$ret[] = array(
						'var' => substr($obj,0,-4),
						'size' => filesize(PDT_WORKING_DIR.'/data/ram/'.$script.'/'.$obj)
					);
				}
			}
			return $ret;
		}else{
			return false;
		}
	}
}
?>