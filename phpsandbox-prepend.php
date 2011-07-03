<?php

/**
 * PHP Sandbox
 * 
 * A PHP sandboxing class to help increase security of unknown scripts
 * This is not the be all and end all of security!
 * 
 * Default auto prepend file to fake a web enviroment and workaround common problems
 * 
 * Requirements: PHP5
 * Copyright (c) 2011 Paul Fryer (www.fryer.org.uk)
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the The GNU Lesser General Public License as published by
 * the Free Software Foundation; version 3 or any latter version of the license.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * The GNU Lesser General Public License (LGPLv3) for more details.
 * 
 * 
 * @package PHPSandbox
 * @author Paul Fryer <paul@fryer.org.uk>
 * @license http://www.opensource.org/licenses/lgpl-3.0.html LGPL
 *
 */

//Common problem so just setting it to something is better than nothing
date_default_timezone_set('UTC');

//If this is setup as root not really useful here and if your running as root your probably up ** creak anyway
//@chroot(dirname(__FILE__));

//Fake standard web server var's if passed in
$session_workaround = false;
$i = 1;
unset($argv[0]);
while ($i < 100 && isset($argv[$i])){
	if(substr($argv[$i], 0, 5) == '_POST'){
		$_POST = unserialize(substr($argv[$i], 6));
		unset($argv[$i]);
	}else if(substr($argv[$i], 0, 4) == '_GET'){
		$_GET = unserialize(substr($argv[$i], 5));
		unset($argv[$i]);
	}else if(substr($argv[$i], 0, 15) == '_SESSWORKAROUND'){
		$session_workaround = true;
		unset($argv[$i]);
	}else if(substr($argv[$i], 0, 10) == '_PHPSESSID'){
		$_COOKIE['PHPSESSID'] = substr($argv[$i], 11);
		session_id($_COOKIE['PHPSESSID']);
		unset($argv[$i]);
	}else if(substr($argv[$i], 0, 8) == '_SESSION'){
		$_SESSION = unserialize(substr($argv[$i], 9));
		if($session_workaround){
			file_put_contents(ini_get('session.save_path').DIRECTORY_SEPARATOR.'sess_'.session_id(), sessionRawEncode($_SESSION));
		}
		unset($argv[$i]);
	}else if (substr($argv[$i], 0, 4) == '_END'){
		unset($argv[$i]);
		break;
	}
	$i++;
}
unset($session_workaround);

//Hide the enviroment veriables to help provide obscurification
foreach($_ENV as $key => $value){
	putenv("$key=null");
	$_ENV[$key]=null;
	unset($_ENV[$key]);
}

foreach($_SERVER as $key => $value){
	$_SERVER[$key]=null;
	unset($_SERVER[$key]);
}


function sessionRawEncode($array, $safe = true){  
    // the session is passed as refernece, even if you dont want it to
    if($safe){
        $array = unserialize(serialize($array));
    }
   
    $raw = '';
    $line = 0;
    $keys = array_keys($array);
    foreach($keys as $key){
        $value = $array[$key];
        $line ++ ;
       
        $raw .= $key.'|';
       
        if(is_array($value) && isset($value['huge_recursion_blocker_we_hope'])) {
            $raw .= 'R:'. $value['huge_recursion_blocker_we_hope'].';';
        } else {
            $raw .= serialize($value) ;
        }
        $array[$key] = Array('huge_recursion_blocker_we_hope' => $line ) ;
    }
   
    return $raw;
}
