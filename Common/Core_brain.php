<?php
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_WARNING);
if(defined('IN_CRONLITE'))return;
define('IN_CRONLITE', true);
define('SYSTEM_ROOT', dirname(__FILE__).'/');
define('ROOT', dirname(SYSTEM_ROOT).'/');
date_default_timezone_set('PRC');
$date = date("Y年m月d日");
include_once(SYSTEM_ROOT."Autoloader.php");
Autoloader::register();

$isLogin = false;
$isUserLogin = false;
$mod = isset($mod) ? $mod : '' ;

if(is_file(SYSTEM_ROOT.'360safe/360webscan.php')){//360网站卫士
    require_once(SYSTEM_ROOT.'360safe/360webscan.php');
}

require SYSTEM_ROOT.'Database_Config.php';
if(!$dbconfig['user']||!$dbconfig['pwd']||!$dbconfig['dbname']){header('Content-type:text/html;charset=utf-8');echo '您还没安装，请<a href="install">立即安装</a>';exit();}
try {
    $DB = new PDO("mysql:host={$dbconfig['host']};dbname={$dbconfig['dbname']};port={$dbconfig['port']}",$dbconfig['user'],$dbconfig['pwd']);
}catch(Exception $e){
	exit('数据库链接失败！');
}
$DB = new \lib\PdoHelper($dbconfig);
$Admin = new \lib\Adminclass();
$Gets = new \lib\Gets();
if($DB->query("select * from nteam_config where 1")==FALSE){header('Content-type:text/html;charset=utf-8');echo '您还没安装，请<a href="install">立即安装</a>';exit();}
include_once(SYSTEM_ROOT."Core_Functions.php");
session_start();

// 設置重要的 HTTP 安全標頭
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: same-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\' https:; style-src \'self\' \'unsafe-inline\' https:; img-src \'self\' data: https:; font-src \'self\' https:;');

if($mod != 'install'){
    if(isset($_SESSION['adminUser'])){
        $adminUser = \lib\Security::escapeHtml($_SESSION['adminUser']);
        $ip = $Gets->ip();
        $city = $Gets->get_city($ip);
        
        $stmt = \lib\Security::bindParams(
            "SELECT * FROM `nteam_admin` WHERE `adminUser` = :adminUser",
            ['adminUser' => $adminUser]
        );
        $stmt->execute();
        $adminData = $stmt->fetch();
        if(!empty($adminData) && $adminData['adminLoginIp'] == $ip)$isLogin = true;
    }
    if(!isset($notLogin) && $mod == 'admin' && !$isLogin)header('Location:./login.php');
}
if (!file_exists(SYSTEM_ROOT.'lib/udatesql.lock')) {
    include(SYSTEM_ROOT."lib/mysql.php");//更新数据库
    file_put_contents(SYSTEM_ROOT.'lib/udatesql.lock', '数据库更新检测文件');//生成数据库检测文件
}
?>