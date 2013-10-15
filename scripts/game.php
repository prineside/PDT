<?php
    $delay = 500;

    $money = 20000;
    $deposit = 0;
    $yearly = 0;
    $year = date('Y');
    $past_years = 0;
    $percent = 12;
    
    $stamp = time();
    $PDT->display('Игра начинается '.date('F').' '.$year);
    $PDT->display('Ставка 12% в год. Каждых 10 лет уменьшается на 1%');
    $PDT->display('Каждый месяц деньги уменьшаются на 20-100');
    $PDT->display('Цель - как можно быстрее заработать 1 000 000');
    $PDT->display('В 2100 году банк закроется и вернет 20-30% депозитного счета');
    $PDT->display('<span>deposit [int]</span> положить на счет','fade');
    $PDT->display('<span>withdraw [int]</span> снять со счета','fade');
    $PDT->display('<span>speed [int]</span> продолжительность суток (мс). Умолчание - 500мс','fade');
    
    while($PDT->running()){
        $stamp += 86400;
        if(date('m',$stamp)==1 && date('d',$stamp)==1){
            $year++;
            $past_years++;
            if(($past_years%10)==0 && $percent>=2){
                $percent--;
                $PDT->display('Ставка: '.$percent.'% / год','warning');
            }
            if($year == 2100){
                $money += (rand(200,300)/1000)*$deposit;
                if($money >= 1000000){
                    $PDT->display('Заработан $1 000 000','success');
                    $PDT->display('Дата: '.date('d F ', $stamp).$year,'success');
                    $PDT->truncate(1);
                }else{
                    $PDT->display('Вам не хватило '.(1000000-$money),'error');
                    $PDT->truncate(1);
                }
            }
            $PDT->display($year,'warning');
            $money += $yearly;
            $PDT->display('Заработано: <green>'.$yearly.'</green>');
            $yearly = 0;
        }
        if(date('d',$stamp)==1){
            $PDT->display(date('F', $stamp));
            $money -= rand(20, 100);
            if($money<0){
                $PDT->display('Конец игры','error');
                $PDT->truncate(1);
            }
            $yearly += $deposit*($percent/1200);
            $PDT->display('Деньги: '.$money.' Депозит: '.$deposit, 'fade');
            if($money >= 1000000){
                $PDT->display('Заработан $1 000 000','success');
                $PDT->display('Дата: '.date('d F ', $stamp).$year,'success');
                $PDT->truncate(1);
            }
        }
        
        $inp = $PDT->input();
        if($inp != ''){
            $cmd_a = explode(' ', $inp);
            if($cmd_a[0] == 'deposit'){
                if($cmd_a[1] > 0){
                    if($cmd_a[1] <= $money){
                        $money -= $cmd_a[1];
                        $deposit += $cmd_a[1];
                        $PDT->display('Деньги: '.$money.' Депозит: '.$deposit);
                    }
                }
            }elseif($cmd_a[0] == 'withdraw'){
                if($cmd_a[1] > 0){
                    if($cmd_a[1] <= $deposit){
                        $deposit -= $cmd_a[1];
                        $money += $cmd_a[1];
                        $PDT->display('Деньги: '.$money.' Депозит: '.$deposit);
                    }
                }
            }elseif($cmd_a[0] == 'speed'){
                if($cmd_a[1] > 0){
                    $delay = $cmd_a[1];
                    $PDT->display('Delay: '.$delay.'ms.');
                }
            }
        }
        $PDT->wait($delay);
    }
?>