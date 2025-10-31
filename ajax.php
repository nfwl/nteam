<?php
include("./Common/Core_brain.php");

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

@header('Content-Type: application/json; charset=UTF-8');

switch($act) {
    case 'add_blog_comment':
        if(!$isLogin) exit('{"code":0,"msg":"請先登錄"}');
        
        $blog_id = intval($_POST['blog_id']);
        $content = trim($_POST['content']);
        
        if($content == '') {
            exit('{"code":0,"msg":"評論內容不能為空"}');
        }
        
        // 檢查博客是否存在
        $blog = $DB->getRow("SELECT id FROM nteam_blogs WHERE id='$blog_id' AND status=1");
        if(!$blog) exit('{"code":0,"msg":"博客不存在或已被刪除"}');
        
        $sql = "INSERT INTO nteam_blog_comments (blog_id,user_id,content,create_time,status) VALUES ('$blog_id','".$userInfo['id']."','".addslashes($content)."',NOW(),1)";
        if($DB->exec($sql)) {
            exit('{"code":1,"msg":"評論成功"}');
        } else {
            exit('{"code":0,"msg":"評論失敗"}');
        }
        break;
        
    default:
        exit('{"code":-4,"msg":"No Act"}');
        break;
}