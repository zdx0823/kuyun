<?php 

define('HOSTNAME', 'localhost');
define('USERNAME', 'root');
define('PASSWORD', 'root');
define('DATABASE', 'kuyun');
define('PREFIX', 'ky_');
define('USER', 'user');
define('FOLDER', 'folder');
define('FILE', 'file');
define('CLA', 'class');
define('MYCLA', 'myclass');
define('COLLECT', 'collect');
//dirname(__DIR__)获取当前文件所在目录详细地址
define('ROOT', str_replace('\\', '/', substr(dirname(dirname(__DIR__)),0,strrpos(dirname(dirname(__DIR__)),'php'))));


// define('BACKUP', '10.21.75.3:8080/newkuyun/');