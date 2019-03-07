<?php
// include_once('php/handle/user.php');
// include_once('php/handle/function.php');
// include_once('php/handle/file.php');
// include_once('php/handle/down.php');
// include_once('php/handle/data.php');
// include_once('php/handle/backup.php');

// $old_filename='F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/测试文件夹-1';
// $new_filename='F:/phpStudy/PHPTutorial/WWW/kuyun/src/data/home/20177715114/bbbbbb';
// var_dump(dirname(dirname(__DIR__)));
// rename(iconv('UTF-8','GBK',$old_filename), iconv('UTF-8','GBK',$new_filename));
// rename($old_filename, $new_filename);




include_once('lib/Db.php');

$db = new Db([
    'host'=>'localhost',
    'dbname'=>'kuyun',
    'name'=>'root',
    'pass'=>'root'
]);

var_dump($_FILES);