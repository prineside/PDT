<?php
	// Создадим скрипт с именем "second", запустим после запуска скрипта "first"
	function scan_script_first($PDT){
		if(!$PDT->running()) $PDT->truncate();
		
		// Получаем массив чужих переменных
		$vars_array = $PDT->getVariableList('first');
			
		if(sizeof($vars_array)>0){
			$PDT->display('Сошпионили список переменных скрипта "first":');
			foreach($vars_array as $k=>$v){
				$PDT->display('Переменная: <blue>'.$k.'</blue>, значение: <green>'.$v.'</green>');
			}
			return $vars_array;
		}else{
			$PDT->display('Не удалось прочитать список переменных');
			$PDT->wait(3000);		// Спим три секунды
			scan_script_first();	// И снова в путь
		}
	}
			
	$vars_array = scan_script_first($PDT);
	
	// После того, как достали список переменных и нас не убили
	if($vars_array['variable'] == 1){
		$PDT->write('first.variable', 'some other value');
		$PDT->display('Изменили переменную другого скрипта');
	}else{
        $PDT->display('Переменная не равна единице');   
	}
?>