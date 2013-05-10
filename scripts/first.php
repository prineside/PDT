<?php
	// Создадим скрипт с именем "first", запустим первым
	$PDT->write('variable',1);
	$var = $PDT->read('variable');
	if($var == 1){
		$PDT->display('Значение сохранено в памяти, ждем изменений');
	}else{
		$PDT->display('Ошибка сохранения в память');
		$PDT->truncate();
	}
	while($PDT->running()){
		// Бесконечный цикл, пока нас не убьют
		if($PDT->read('variable') != 1){
			// Это скрипт "second" поменял нам значение
			$PDT->display('Наше значение поменяли на <blue>'.$PDT->read('variable').'</blue>');
			break;
		}
		$PDT->wait(2000);	// Спим две секунды
	}
?>