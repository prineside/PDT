<?php
/*
	$url - адресс для отправки
	$data - POST-массив
*/
function multiCurl($url, $data, $options = array()){
    $curls = array();
    $result = array();
    $mh = curl_multi_init();
    foreach ($data as $id=>$post) {
        $curls[$id] = curl_init();
        curl_setopt($curls[$id], CURLOPT_URL,            	$url);
		curl_setopt($curls[$id], CURLOPT_FOLLOWLOCATION, 	0);
		curl_setopt($curls[$id], CURLOPT_CONNECTTIMEOUT, 	12);
        curl_setopt($curls[$id], CURLOPT_HEADER,         	1);
        curl_setopt($curls[$id], CURLOPT_NOBODY,         	1);
        curl_setopt($curls[$id], CURLOPT_RETURNTRANSFER, 	1);
        curl_setopt($curls[$id], CURLOPT_POST,       		1);
        curl_setopt($curls[$id], CURLOPT_POSTFIELDS, 		$post);
		
        if (count($options)>0) curl_setopt_array($curls[$id], $options);
        curl_multi_add_handle($mh, $curls[$id]);
    }
	
    $running = null;
    do { curl_multi_exec($mh, $running); } while($running > 0);
    foreach($curls as $id => $c){
        # $result[$id] = curl_multi_getcontent($c);
		# $result[$id] = curl_getinfo($c, CURLINFO_EFFECTIVE_URL);
		preg_match("!Location: (.*)!", curl_multi_getcontent($c), $matches);
		$result[$id] = $matches[1];
        curl_multi_remove_handle($mh, $c);
    }
    curl_multi_close($mh);
    return $result;
}
?>