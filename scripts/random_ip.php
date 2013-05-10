<?php
    $ip_array = array();
    $running = true;
    
    while($PDT->running()){
        $input = $PDT->input();
        if($input){
            if($input == 'pause'){
                $running = false;
            }elseif($input == 'continue'){
                $running = true;
            }
        }
        if($running){
            $numbers = array();
            for($i=0; $i<4; $i++){
                $numbers[] = rand(0,255);
            }
            $ip = implode('.',$numbers);
            if(!in_array($ip, $ip_array)){
                $ip_array[] = $ip;
                $PDT->display('Сгенерировали новый IP: <blue>'.$ip.'</blue>');
            }else{
                $PDT->display('IP уже был: <red>'.$ip.'</red>');
            }
        }
        $PDT->wait(1000);
    }
?>