<?php
	for($i=1; $i<=1024; $i++){
		if(!$PDT->running()) break;

		$con = @fsockopen("127.0.0.1", $i, $eroare, $eroare_str, 1);
		if($con){
			$PDT->display('Нашел открытый порт: <red>'.$i.'</red>');
		}
		if($i%64){}else{
			$PDT->display('Текущий порт: <blue>'.$i.'</blue>');
		}
	}
?>