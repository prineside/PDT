<?php
/*
Оглашаем константы и некоторые функции...
*/
function PDT_GetRootLink(){
	$arr = explode('/',$_SERVER['SERVER_NAME'].$_SERVER['PHP_SELF']);
	$href = 'http://';
	for($i=0; $i<sizeof($arr)-1; $i++){
		$href.=$arr[$i].'/';
	}
	return $href;
}

function PDT_IncludeTemplate($template){
	if(is_file(PDT_WORKING_DIR.'/media/templates/'.$template)){
		include_once(PDT_WORKING_DIR.'/media/templates/'.$template);
	}else{
		PDT_HandleError('Template <b>'.$template.'</b> not found');
	}
}

function ErrorHandler($errno, $errstr, $errfile, $errline){
	if(!$errno || $errno == 2){ return false; }
	$src = fopen('pdt_error.log','a');
	fwrite($src, json_encode(array(
		'TIME' => time(),
		'ERRNO' => $errno,
		'ERRSTR' => $errstr,
		'ERRFILE' => $errfile,
		'ERRLINE' => $errline
	))."\r\n");
	fclose($src);
}

function PDT_HandleError($error){
	if(!$_POST['shell']){
		PDT_IncludeTemplate('pdt_error.html');
	}
	$debug = array_reverse(debug_backtrace());
	for($i=0; $i<sizeof($debug)-1; $i++){
		$args = '';
		if(is_array($debug[$i]['args'])){
			$args = implode(", ", $debug[$i]['args']);
		}
		if($debug[$i]['class']){
			$debug[$i]['function'] = $debug[$i]['class'].'->'.$debug[$i]['function'];
		}
		$route .= '<div class="pdt_error_function">'.$debug[$i]['function'].'('.$args.')</div>';
	}
	
	$src = fopen(PDT_WORKING_DIR.'/pdt_error.log', 'a');
	fwrite($src, date('d.m.Y H:i:s').' - '.$error."\r\n");
	fclose($src);
	
	echo '
	<div class="pdt_error">
		<div class="pdt_error_message">'.$error.'</div>
		<div class="pdt_error_file">Файл: '.$debug[sizeof($debug)-1]['file'].'</div>
		<div class="pdt_error_route">'.$route.'<div class="pdt_clear"></div></div>
	</div>';
}

function my_json_encode($arr){
        //convmap since 0x80 char codes so it takes all multibyte codes (above ASCII 127). So such characters are being "hidden" from normal json_encoding
        array_walk_recursive($arr, function (&$item, $key) { if (is_string($item)) $item = mb_encode_numericentity($item, array (0x80, 0xffff, 0, 0xffff), 'UTF-8'); });
        return mb_decode_numericentity(json_encode($arr), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');

}
?>