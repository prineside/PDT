<?php
	echo json_encode($PDT->read($_POST['args']['script'].'.'.$_POST['args']['var']));
?>