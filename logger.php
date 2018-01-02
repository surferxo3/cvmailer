<?php

/*#############################
* Developer: Mohammad Sharaf Ali
* Description: Script to track pixel and log in db
* Date: 23-04-2016
*/#############################

##################### SCRIPT SETTINGS #####################
ini_set('max_execution_time', 0);
ini_set('memory_limit', '3G');

date_default_timezone_set('Asia/Karachi');

##################### CONSTANTS #####################
//db constants
const DB_HOST = 'your_db_host';
const DB_USER = 'your_db_user';
const DB_PASS = 'your_db_pass';
const DB_NAME = 'your_db_name';
const DB_TABLE = 'companies';
const DB_DSN = 'mysql:dbname='. DB_NAME. ';host='. DB_HOST;


##################### LIBRARIES #####################
require_once 'db/NotORM.php';


##################### DB CONNECTION #####################
$structure = new NotORM_Structure_Convention(
    $primary = 'ID',
    $foreign = '%s_ID',
    $table = '%s',
    $prefix = '');

$pdo = new PDO(DB_DSN, DB_USER, DB_PASS);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db = new NotORM($pdo, $structure);


##################### MAIN #####################
$logData = isset($_GET['v']) ? $_GET['v'] : '';
list($email, $timestamp) = explode('::', base64_decode($logData)); // datetime for unique url. can use other random generators too...  

$db->{DB_TABLE}()
   ->where('Email = ?', $email)
   ->where('IsOpened = ?', '0')
   ->where('IsActive = ?', '1')
   ->update(array(
   		'IsOpened' => '1',
   		'IsOpenedDT' => date('Y-m-d H:i:s')
	));

#echo 1x1 Transparent Image (Way 1)
header('Content-Type: image/gif');
echo base64_decode('R0lGODlhAQABAJAAAP8AAAAAACH5BAUQAAAALAAAAAABAAEAAAICBAEAOw==');

#echo image to display in client browser (Way 2)
/*
$logo = "templates/images/my_thumb.jpg";
$etag = md5_file($logo);
$lastModified = gmdate('D, d M Y H:i:s', filemtime($logo)) . ' GMT';
 
header('Content-Type: image/jpg');
header('Content-Disposition: inline; filename="examplelogo.png"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($logo));
header('Accept-Ranges: bytes');
header('ETag: "' . $etag . '"');
header('Last-Modified: ' . $lastModified);
 
readfile($logo);
*/
