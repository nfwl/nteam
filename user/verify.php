<?php
include("../Common/Core_brain.php");

$token = isset($_GET['token']) ? addslashes($_GET['token']) : null;
if(!$token) {
    exit('參數錯誤');
}

$user = $DB->getRow("SELECT * FROM nteam_users WHERE verify_token=:token LIMIT 1", [':token'=>$token]);
if(!$user) {
    exit('驗證鏈接無效');
}

if($user['email_verified'] == 1) {
    exit('郵箱已驗證');
}

if(strtotime($user['verify_token_expire']) < time()) {
    exit('驗證鏈接已過期');
}

$stmt = $DB->prepare("UPDATE nteam_users SET email_verified=1,verify_token=NULL,verify_token_expire=NULL WHERE id=:id");
if($stmt->execute([':id'=>$user['id']])) {
    header('Location: profile.php');
} else {
    exit('系統錯誤');
}
?