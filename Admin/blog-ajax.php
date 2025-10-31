<?php
$notLogin = true;
include('../Common/Core_brain.php');

$act=isset($_GET['act'])?daddslashes($_GET['act']):null;

@header('Content-Type: application/json; charset=UTF-8');

switch($act){
    case 'save_blog':
        if(!$isLogin)exit('{"code":0,"msg":"未登录"}');
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $title = addslashes($_POST['title']);
        $category_id = intval($_POST['category_id']);
        $summary = addslashes($_POST['summary']);
        $content = addslashes($_POST['content']);
        $tags = addslashes($_POST['tags']);
        $status = intval($_POST['status']);
        
        if($title == '' || $category_id == 0 || $content == '') {
            exit('{"code":0,"msg":"請確保標題、分類和內容都不為空"}');
        }
        
        if($id) { // 編輯
            $sql = "UPDATE nteam_blogs SET title='$title',category_id='$category_id',summary='$summary',content='$content',tags='$tags',status='$status',update_time=NOW() WHERE id='$id'";
            if($DB->exec($sql)) {
                $DB->query("INSERT INTO `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) VALUES ('" . $_SESSION['adminUser'] . "','編輯了標題為".$title."的博客', NOW(), '".$ip."', '".$city."')");
                exit('{"code":1,"msg":"編輯博客成功"}');
            } else {
                exit('{"code":0,"msg":"編輯博客失敗"}');
            }
        } else { // 新增
            $sql = "INSERT INTO nteam_blogs (title,category_id,summary,content,tags,status,author_id,create_time,update_time) VALUES ('$title','$category_id','$summary','$content','$tags','$status','".$adminData['id']."',NOW(),NOW())";
            if($DB->exec($sql)) {
                $DB->query("INSERT INTO `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) VALUES ('" . $_SESSION['adminUser'] . "','發布了標題為".$title."的博客', NOW(), '".$ip."', '".$city."')");
                exit('{"code":1,"msg":"發布博客成功"}');
            } else {
                exit('{"code":0,"msg":"發布博客失敗"}');
            }
        }
        break;

    case 'delete_blog':
        if(!$isLogin)exit('{"code":0,"msg":"未登录"}');
        
        $id = intval($_POST['id']);
        $blog = $DB->getRow("SELECT title FROM nteam_blogs WHERE id='$id'");
        if(!$blog) exit('{"code":0,"msg":"博客不存在"}');
        
        // 開始事務
        $DB->beginTransaction();
        try {
            // 刪除評論
            $DB->exec("DELETE FROM nteam_blog_comments WHERE blog_id='$id'");
            // 刪除博客
            $DB->exec("DELETE FROM nteam_blogs WHERE id='$id'");
            
            $DB->query("INSERT INTO `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) VALUES ('" . $_SESSION['adminUser'] . "','刪除了標題為".$blog['title']."的博客', NOW(), '".$ip."', '".$city."')");
            
            $DB->commit();
            exit('{"code":1,"msg":"刪除博客成功"}');
        } catch (Exception $e) {
            $DB->rollBack();
            exit('{"code":0,"msg":"刪除博客失敗"}');
        }
        break;

    case 'toggle_blog_status':
        if(!$isLogin)exit('{"code":0,"msg":"未登录"}');
        
        $id = intval($_POST['id']);
        $blog = $DB->getRow("SELECT title,status FROM nteam_blogs WHERE id='$id'");
        if(!$blog) exit('{"code":0,"msg":"博客不存在"}');
        
        $status = $blog['status'] ? 0 : 1;
        $sql = "UPDATE nteam_blogs SET status='$status' WHERE id='$id'";
        if($DB->exec($sql)) {
            $action = $status ? '顯示' : '隱藏';
            $DB->query("INSERT INTO `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) VALUES ('" . $_SESSION['adminUser'] . "','".$action."了標題為".$blog['title']."的博客', NOW(), '".$ip."', '".$city."')");
            exit('{"code":1,"msg":"操作成功"}');
        } else {
            exit('{"code":0,"msg":"操作失敗"}');
        }
        break;

    case 'save_blog_category':
        if(!$isLogin)exit('{"code":0,"msg":"未登录"}');
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $name = addslashes($_POST['name']);
        $description = addslashes($_POST['description']);
        $sort = intval($_POST['sort']);
        
        if($name == '') {
            exit('{"code":0,"msg":"請輸入分類名稱"}');
        }
        
        if($id) { // 編輯
            $sql = "UPDATE nteam_blog_categories SET name='$name',description='$description',sort='$sort' WHERE id='$id'";
            if($DB->exec($sql)) {
                $DB->query("INSERT INTO `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) VALUES ('" . $_SESSION['adminUser'] . "','編輯了博客分類：".$name."', NOW(), '".$ip."', '".$city."')");
                exit('{"code":1,"msg":"編輯分類成功"}');
            } else {
                exit('{"code":0,"msg":"編輯分類失敗"}');
            }
        } else { // 新增
            $sql = "INSERT INTO nteam_blog_categories (name,description,sort) VALUES ('$name','$description','$sort')";
            if($DB->exec($sql)) {
                $DB->query("INSERT INTO `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) VALUES ('" . $_SESSION['adminUser'] . "','新增了博客分類：".$name."', NOW(), '".$ip."', '".$city."')");
                exit('{"code":1,"msg":"新增分類成功"}');
            } else {
                exit('{"code":0,"msg":"新增分類失敗"}');
            }
        }
        break;

    case 'delete_blog_category':
        if(!$isLogin)exit('{"code":0,"msg":"未登录"}');
        
        $id = intval($_POST['id']);
        
        // 檢查分類是否存在
        $category = $DB->getRow("SELECT name FROM nteam_blog_categories WHERE id='$id'");
        if(!$category) exit('{"code":0,"msg":"分類不存在"}');
        
        // 檢查分類下是否有文章
        $blog_count = $DB->getColumn("SELECT COUNT(*) FROM nteam_blogs WHERE category_id='$id'");
        if($blog_count > 0) {
            exit('{"code":0,"msg":"該分類下還有".$blog_count."篇文章，請先移動或刪除這些文章"}');
        }
        
        $sql = "DELETE FROM nteam_blog_categories WHERE id='$id'";
        if($DB->exec($sql)) {
            $DB->query("INSERT INTO `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) VALUES ('" . $_SESSION['adminUser'] . "','刪除了博客分類：".$category['name']."', NOW(), '".$ip."', '".$city."')");
            exit('{"code":1,"msg":"刪除分類成功"}');
        } else {
            exit('{"code":0,"msg":"刪除分類失敗"}');
        }
        break;

    default:
        exit('{"code":-4,"msg":"No Act"}');
        break;
}