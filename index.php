<?php
include('inc/Stock.php');
include('inc/PseudoDaemon.class.php');

define(PDT_ROOT_LINK, PDT_GetRootLink());
define(PDT_WORKING_DIR, dirname(__FILE__));

$PDT = new PseudoDaemon();

if($_POST['handler']){
	if(is_file(PDT_WORKING_DIR.'/handlers/'.$_POST['handler'].'.php')){
		include(PDT_WORKING_DIR.'/handlers/'.$_POST['handler'].'.php');
	}else{
		PDT_HandleError('Handler <b>'.$_POST['handler'].'</b> not found');
	}
}elseif($_POST){
	PDT_HandleError('Неверный запрос');
}else{
	include(PDT_WORKING_DIR.'/media/templates/pdt_index.html');
}
?>