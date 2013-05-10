<?php
	$PDT->script_name = $_POST['args']['script'];
	$PDT->display($_POST['args']['data'], 'input');
	file_put_contents(PDT_WORKING_DIR.'/data/scripts/input/'.$_POST['args']['script'].'.var', stripslashes($_POST['args']['data']));
?>