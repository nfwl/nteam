<?php
$notLogin = true;
include('../Common/Core_brain.php');

$act=isset($_GET['act'])?daddslashes($_GET['act']):null;

@header('Content-Type: application/json; charset=UTF-8');

switch($act){
    case 'login':
        $adminUser = addslashes($_POST['adminUser']);
        $adminPwd = md5($_POST['adminPwd']);
        if (conf('Turnstile_Open') == 1) {
            $token = isset($_POST['token'])?$_POST['token']:null;
        }
        if ($adminUser=='' || $adminPwd=='') {
            exit('{"code":0,"msg":"请确保每项都不为空"}');
        }
        if (conf('Turnstile_Open') == 1 && $token=='') {
            exit('{"code":0,"msg":"请先完成人机验证"}');
        }
        // 如果开启 Turnstile，进行服务器端验证
        if (conf('Turnstile_Open') == 1) {
            $ip = $Gets->ip();
            $secret = conf('Turnstile_Secret');
            $resp = get_curl('https://challenges.cloudflare.com/turnstile/v0/siteverify', http_build_query(array('secret'=>$secret,'response'=>$token,'remoteip'=>$ip)));
            $json = json_decode($resp, true);
            if (!$json || empty($json['success'])) {
                exit('{"code":0,"msg":"人机验证失败，请重试"}');
            }
        }
        else{
            $adminData = $Admin->getAdmin($adminUser);
            
            if(empty($adminData))exit('{"code":0,"msg":"管理员不存在"}');
            if($adminPwd != $adminData['adminPwd'])exit('{"code":0,"msg":"密码错误"}');
    
            $Admin->loginAdmin($adminUser);
            $_SESSION['adminUser'] = $adminUser;
            $_SESSION['adminQq'] = $adminData['adminQq'];
            $ip = $Gets->ip();
            $city = $Gets->get_city($ip);
            $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','登录后台中心', NOW(), '".$ip."', '".$city."')");
            exit('{"code":1,"msg":"登录成功，请稍后..."}');
        }
    break;
    case 'admininfo':
        if(!$isLogin)exit('{"code":0,"msg":"未登录"}');
        $id = $adminData['id'];
        if (conf('Turnstile_Open') == 1) {
            $token = isset($_POST['token'])?$_POST['token']:null;
        }
        $adminUser = $adminData['adminUser'];
        $adminQq = addslashes($_POST['adminQq']);
        if (conf('Turnstile_Open') == 1 && $token=='') {
            exit('{"code":0,"msg":"请先完成人机验证"}');
        }
        if (conf('Turnstile_Open') == 1) {
            $ip = $Gets->ip();
            $secret = conf('Turnstile_Secret');
            $resp = get_curl('https://challenges.cloudflare.com/turnstile/v0/siteverify', http_build_query(array('secret'=>$secret,'response'=>$token,'remoteip'=>$ip)));
            $json = json_decode($resp, true);
            if (!$json || empty($json['success'])) {
                exit('{"code":0,"msg":"人机验证失败，请重试"}');
            }
        }
        $adminData = $Admin->getAdmin($adminUser);
        $AdminData = $Admin->getAdminName($adminUser);
        if ($adminQq == $adminData['adminQq']) {
            exit('{"code":0,"msg":"未修改数据，无需保存！"}');
        }
        
        $sql = "UPDATE `nteam_admin` SET `adminUser` = '$adminUser',`adminQq` = '$adminQq' WHERE `id` = '$id'";
        if(!empty($_POST['adminPwd'])){
            $adminPwd = md5($_POST['adminPwd']);
            if($adminPwd == $adminData['adminPwd'])exit('{"code":0,"msg":"与原密码相同！"}');
            $sql = "UPDATE `nteam_admin` SET `adminPwd` = '$adminPwd',`adminUser` = '$adminUser',`adminQq` = '$adminQq' WHERE `id` = '$id'";
        }
        $admininfo = $DB->exec($sql);
        if(!$admininfo)exit('{"code":0,"msg":"修改失败，未知错误。"}');
        if(!empty($_POST['adminPwd'])){$DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','修改账号信息', NOW(), '".$ip."', '".$city."')");unset($_SESSION['adminUser']);}
        $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','修改账号信息', NOW(), '".$ip."', '".$city."')");
        if(!empty($_POST['adminPwd'])){
            exit('{"code":2,"msg":"修改成功"}');
        }else{
            exit('{"code":1,"msg":"修改成功"}');
        }
    break;
    case 'setProject':
        $type=addslashes($_GET['type']);
        $id=intval($_GET['id']);
        $status=intval($_GET['status']);
        $num=intval($_GET['num']);
        if ($type == 'Status') {
            $sql = "UPDATE nteam_project_list SET status='$status' WHERE id='$id'";
            $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','修改了项目ID为".$id."的网站状态', NOW(), '".$ip."', '".$city."')");
            if($DB->exec($sql)!==false)exit('{"code":0,"msg":"修改网站状态成功！"}');
            else exit('{"code":-1,"msg":"修改网站状态失败['.$DB->error().']"}');
        }elseif ($type == 'Show') {
            $sql = "UPDATE nteam_project_list SET is_show='$num' WHERE id='$id'";
            $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','修改了项目ID为".$id."的显示状态', NOW(), '".$ip."', '".$city."')");
            if($DB->exec($sql)!==false)exit('{"code":0,"msg":"修改成功！"}');
            else exit('{"code":-1,"msg":"修改网站状态失败['.$DB->error().']"}');
        }elseif ($type == 'Audit_status') {
            // 先獲取項目信息
            $project = $DB->getRow("SELECT * FROM nteam_project_list WHERE id='$id' LIMIT 1");
            if (!$project) {
                exit('{"code":-1,"msg":"项目不存在"}');
            }

            // 更新審核狀態
            $sql = "UPDATE nteam_project_list SET Audit_status='$status' WHERE id='$id'";
            if($DB->exec($sql) === false) {
                exit('{"code":-1,"msg":"修改审核状态失败['.$DB->error().']"}');
            }

            // 記錄日誌
            $DB->query("INSERT INTO `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) VALUES ('" . $_SESSION['adminUser'] . "','修改了项目ID为".$id."的审核状态', NOW(), '".$ip."', '".$city."')");

            // 發送通知郵件
            if($status == 1) { // 審核通過
                $sub = '项目审核通过通知';
                $msg = get_email_template(
                    '项目审核通过',
                    "亲爱的用户：\n\n".
                    "您提交的项目已通过审核！\n\n".
                    "【项目信息】\n".
                    "• 名称：{$project['name']}\n".
                    "• 网址：{$project['url']}\n".
                    "• 类型：{$project['type']}\n\n".
                    "项目简介：\n{$project['sketch']}\n\n".
                    "您的项目现在已经可以在网站上展示了。如需修改项目信息，请联系管理员。\n\n".
                    "感谢您的参与和支持！",
                    array(
                        'text' => '查看项目详情',
                        'url' => 'http://' . conf('Url') . '/project.php'
                    )
                );
            } else if($status == 2) { // 審核拒絕
                $sub = '项目审核未通过通知';
                $msg = get_email_template(
                    '项目审核未通过',
                    "亲爱的用户：\n\n".
                    "很抱歉，您提交的项目未能通过审核。\n\n".
                    "【项目信息】\n".
                    "• 名称：{$project['name']}\n".
                    "• 网址：{$project['url']}\n".
                    "• 提交时间：{$project['intime']}\n\n".
                    "您可以根据以下建议修改后重新提交：\n".
                    "• 确保项目内容符合相关规定\n".
                    "• 完善项目描述和展示信息\n".
                    "• 确保项目链接可正常访问\n\n".
                    "如有任何疑问，请通过网站留言功能与我们联系。",
                    array(
                        'text' => '重新提交项目',
                        'url' => 'http://' . conf('Url') . '/project.php'
                    )
                );
            }

            // 如果存在郵件內容則發送
            if(isset($sub) && isset($msg)) {
                $admin = $DB->query("SELECT * FROM nteam_admin WHERE id=1")->fetch();
                if($admin) {
                    $qq = $admin['adminQq'];
                    send_mail($qq.'@qq.com', $sub, $msg);
                }
            }

            exit('{"code":0,"msg":"修改审核状态成功！"}');
        }elseif ($type == 'Del') {
            $id=intval($_POST['id']);
            $rows=$DB->getRow("select * from nteam_project_list where id='$id' limit 1");
            if(!$rows)exit('{"code":-1,"msg":"项目不存在"}');
            $sql="DELETE FROM nteam_project_list WHERE id='$id'";
            if(!$DB->exec($sql)){exit('{"code":-1,"msg":"删除项目失败！"}');}else{
            $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','删除了ID为".$id."的项目', NOW(), '".$ip."', '".$city."')");
            exit('{"code":0,"msg":"删除项目成功！"}');}
        }elseif ($type == 'Add') {
            $name=$_POST['name'];
            $url=$_POST['url'];
            $img=$_POST['img'];
            $sketch=$_POST['sketch'];
            $descriptison=$_POST['descriptison'];
            $type=$_POST['type'];
            $is_show=$_POST['is_show'];
            $Audit_status=$_POST['Audit_status'];
            $status=$_POST['status'];
            if($adminData['adminRank']==2){
                if ($name==NULL || $url==NULL || $img==NULL || $sketch==NULL || $descriptison==NULL || $type==NULL || $is_show==NULL || $status==NULL) {
                    exit('{"code":-1,"msg":"保存错误,请确保每项都不为空!"}');
                }
                $sds=$DB->exec("INSERT INTO `nteam_project_list` (`name`, `url`, `img`, `sketch`, `descriptison`, `type`, `intime`, `status`, `Audit_status`, `is_show`) VALUES ('{$name}', '{$url}', '{$img}', '{$sketch}', '{$descriptison}', '{$type}', '{$date}', '{$status}', '0', '{$is_show}')");
                $id=$DB->lastInsertId();
                if($sds){
                    $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','申请了名为".$name."的项目', NOW(), '".$ip."', '".$city."')");
                    exit('{"code":0,"msg":"添加项目成功！"}');
                }else{
                    exit('{"code":-1,"msg":"添加项目失败！"}');
                }
            }elseif ($adminData['adminRank']==1) {
                if ($name==NULL || $url==NULL || $img==NULL || $sketch==NULL || $descriptison==NULL || $type==NULL || $is_show==NULL || $Audit_status==NULL || $status==NULL) {
                    exit('{"code":-1,"msg":"保存错误,请确保每项都不为空!"}');
                }
                $sds=$DB->exec("INSERT INTO `nteam_project_list` (`name`, `url`, `img`, `sketch`, `descriptison`, `type`, `intime`, `status`, `Audit_status`, `is_show`) VALUES ('{$name}', '{$url}', '{$img}', '{$sketch}', '{$descriptison}', '{$type}', '{$date}', '{$status}', '{$Audit_status}', '{$is_show}')");
                $id=$DB->lastInsertId();
                if($sds){
                    $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','添加了名为".$name."的项目', NOW(), '".$ip."', '".$city."')");
                    exit('{"code":0,"msg":"添加项目成功！"}');
                }else{
                    exit('{"code":-1,"msg":"添加项目失败！"}');
                }
            }
        }elseif ($type == 'Edit') {
            $id=$_GET['id'];
            $rows=$DB->getRow("select * from nteam_project_list where id='$id' limit 1");
            if(!$rows)exit('{"code":-1,"msg":"当前项目不存在！"}');
            $name=$_POST['name'];
            $url=$_POST['url'];
            $img=$_POST['img'];
            $sketch=$_POST['sketch'];
            $descriptison=$_POST['descriptison'];
            $type=$_POST['type'];
            $is_show=$_POST['is_show'];
            $Audit_status=$_POST['Audit_status'];
            $status=$_POST['status'];
            if($adminData['adminRank']==2){
                if($name==NULL || $url==NULL || $img==NULL || $sketch==NULL || $descriptison==NULL || $type==NULL || $is_show==NULL || $status==NULL){
                    exit('{"code":-1,"msg":"保存错误,请确保每项都不为空!"}');
                }
                $sql="update `nteam_project_list` set `name` ='{$name}',`url` ='{$url}',`img` ='{$img}',`sketch` ='{$sketch}',`descriptison` ='{$descriptison}',`type` ='{$type}',`status` ='{$status}',`is_show` ='{$is_show}' where `id`='$id'";
                if($DB->exec($sql)!==false||$sqs){
                    $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','修改了ID为".$id."的项目', NOW(), '".$ip."', '".$city."')");
                    exit('{"code":0,"msg":"修改项目信息成功！"}');
                }else{
                    exit('{"code":-1,"msg":"修改项目信息失败！"}');
                }
            }elseif ($adminData['adminRank']==1) {
                if($name==NULL || $url==NULL || $img==NULL || $sketch==NULL || $descriptison==NULL || $type==NULL || $is_show==NULL || $Audit_status==NULL || $status==NULL){
                    exit('{"code":-1,"msg":"保存错误,请确保每项都不为空!"}');
                }
                $sql="update `nteam_project_list` set `name` ='{$name}',`url` ='{$url}',`img` ='{$img}',`sketch` ='{$sketch}',`descriptison` ='{$descriptison}',`type` ='{$type}',`status` ='{$status}',`Audit_status` ='{$Audit_status}',`is_show` ='{$is_show}' where `id`='$id'";
                if($DB->exec($sql)!==false||$sqs){
                    $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','修改了ID为".$id."的项目', NOW(), '".$ip."', '".$city."')");
                    exit('{"code":0,"msg":"修改项目信息成功！"}');
                }else{
                    exit('{"code":-1,"msg":"修改项目信息失败！"}');
                }
            }
        }else{
            exit('{"code":-1,"msg":"你在想Peach？"}');
        }
    break;
    case 'setMember':
        if($adminData['adminRank']!=1){exit('{"code":-1,"msg":"您的账号没有权限使用此功能！"}');}
        $type=addslashes($_GET['type']);
        $id=intval($_GET['id']);
        $status=intval($_GET['status']);
        $num=intval($_GET['num']);
        if ($type == 'Status') {
            // 先獲取成員信息
            $member = $DB->getRow("SELECT * FROM nteam_team_member WHERE id='$id' LIMIT 1");
            if (!$member) {
                exit('{"code":-1,"msg":"成员不存在"}');
            }

            // 更新審核狀態
            $sql = "UPDATE nteam_team_member SET Audit_status='$status' WHERE id='$id'";
            if($DB->exec($sql) === false) {
                exit('{"code":-1,"msg":"修改审核状态失败['.$DB->error().']"}');
            }

            // 記錄日誌
            $DB->query("INSERT INTO `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) VALUES ('" . $_SESSION['adminUser'] . "','修改了成员ID为".$id."的审核状态', NOW(), '".$ip."', '".$city."')");

            // 發送通知郵件
            if($status == 1) { // 審核通過
                $sub = '团队成员申请通过通知';
                $msg = get_email_template(
                    '欢迎加入团队',
                    "亲爱的 {$member['name']}：\n\n".
                    "恭喜您！您的团队成员申请已通过审核。\n\n".
                    "【成员信息】\n".
                    "• 昵称：{$member['name']}\n".
                    "• QQ号：{$member['qq']}\n".
                    "• 加入时间：" . date('Y-m-d H:i:s') . "\n\n".
                    "您现在已经是我们团队的正式成员了！\n\n".
                    "【温馨提示】\n".
                    "• 请及时加入团队的QQ群进行交流\n".
                    "• 关注团队最新动态和项目进展\n".
                    "• 积极参与团队活动和项目开发\n\n".
                    "我们期待与您一起成长，共同进步！",
                    array(
                        'text' => '访问团队主页',
                        'url' => 'http://' . conf('Url')
                    )
                );
            } else if($status == 2) { // 審核拒絕
                $sub = '团队成员申请未通过通知';
                $msg = get_email_template(
                    '申请未通过通知',
                    "亲爱的 {$member['name']}：\n\n".
                    "感谢您申请加入我们的团队。经过认真考虑，很遗憾地通知您，您的申请未能通过审核。\n\n".
                    "【申请信息】\n".
                    "• 昵称：{$member['name']}\n".
                    "• QQ号：{$member['qq']}\n".
                    "• 申请时间：{$member['intime']}\n\n".
                    "建议您：\n".
                    "• 完善个人介绍和技能描述\n".
                    "• 提供更多的项目经验和作品展示\n".
                    "• 明确说明您能为团队做出的贡献\n\n".
                    "您可以在合适的时候重新提交申请。如有任何疑问，欢迎通过网站留言与我们联系。\n\n".
                    "祝您发展顺利！",
                    array(
                        'text' => '重新申请',
                        'url' => 'http://' . conf('Url') . '/#join'
                    )
                );
            }

            // 如果存在郵件內容則發送
            if(isset($sub) && isset($msg)) {
                send_mail($member['qq'].'@qq.com', $sub, $msg);
            }

            exit('{"code":0,"msg":"修改审核状态成功！"}');
        }elseif ($type == 'Show') {
            $sql = "UPDATE nteam_team_member SET is_show='$num' WHERE id='$id'";
            $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','修改了成员ID为".$id."的显示状态', NOW(), '".$ip."', '".$city."')");
            if($DB->exec($sql)!==false)exit('{"code":0,"msg":"修改成功！"}');
            else exit('{"code":-1,"msg":"修改网站状态失败['.$DB->error().']"}');
        }elseif ($type == 'Del') {
            $id=intval($_POST['id']);
            $rows=$DB->getRow("select * from nteam_team_member where id='$id' limit 1");
            if(!$rows)exit('{"code":-1,"msg":"成员不存在"}');
            $sql="DELETE FROM nteam_team_member WHERE id='$id'";
            if(!$DB->exec($sql)){exit('{"code":-1,"msg":"删除成员失败！"}');}else{
            $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','删除了ID为".$id."的成员', NOW(), '".$ip."', '".$city."')");
            exit('{"code":0,"msg":"删除成员成功！"}');}
        }elseif ($type == 'Add') {
            $name=$_POST['name'];
            $qq=$_POST['qq'];
            $describe=$_POST['describe'];
            $is_show=$_POST['is_show'];
            $Audit_status=$_POST['Audit_status'];
            if($name==NULL || $qq==NULL || $describe==NULL || $is_show==NULL || $Audit_status==NULL){
                exit('{"code":-1,"msg":"保存错误,请确保每项都不为空!"}');
            } else {
                $sds=$DB->query("INSERT INTO `nteam_team_member` (`name`, `qq`, `describe`, `is_show`, `Audit_status`, `intime`) VALUES ('{$name}', '{$qq}', '{$describe}', '{$is_show}', '{$Audit_status}', NOW())");
                $id=$DB->lastInsertId();
                if($sds){
                    $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','添加了一位名为".$name."的成员', NOW(), '".$ip."', '".$city."')");
                    exit('{"code":0,"msg":"添加成员成功！"}');
                }else{
                    exit('{"code":-1,"msg":"添加成员失败！"}');
                }
            }
        }elseif ($type == 'Edit') {
            $id=$_GET['id'];
            $rows=$DB->getRow("select * from nteam_team_member where id='$id' limit 1");
            if(!$rows)
              exit('{"code":-1,"msg":"当前成员不存在！"}');
            $name=$_POST['name'];
            $qq=$_POST['qq'];
            $describe=$_POST['describe'];
            $is_show=$_POST['is_show'];
            $Audit_status=$_POST['Audit_status'];
            if($name==NULL || $qq==NULL || $describe==NULL || $is_show==NULL || $Audit_status==NULL){
                exit('{"code":-1,"msg":"保存错误,请确保每项都不为空!"}');
            } else {
                $sql="update `nteam_team_member` set `name` ='{$name}',`qq` ='{$qq}',`describe` ='{$describe}',`is_show` ='{$is_show}',`Audit_status` ='{$Audit_status}' where `id`='$id'";
                if($DB->exec($sql)!==false||$sqs){
                    $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','修改了一位名为".$name."的成员信息', NOW(), '".$ip."', '".$city."')");
                    exit('{"code":0,"msg":"修改成员信息成功！"}');
                }else{
                    exit('{"code":-1,"msg":"修改成员信息失败！"}');
                }
            }
        }else{
            exit('{"code":-1,"msg":"你在想Peach？"}');
        }
    break;
    case 'set':
        if($adminData['adminRank']!=1){exit('{"code":-1,"msg":"您的账号没有权限使用此功能！"}');}
        foreach($_POST as $k=>$v){
            saveSetting($k, $v);
        }
        if(saveSetting($k, $v) !== false){$DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','修改了网站配置', NOW(), '".$ip."', '".$city."')");exit('{"code":0,"msg":"设置保存成功！"}');
        }else{ exit('{"code":-1,"msg":"修改设置失败['.$DB->error().']"}');}
    break;
    case 'sets':
        if($adminData['id']!=1){exit('{"code":-1,"msg":"您的账号没有权限使用此功能！"}');}
        foreach($_POST as $k=>$v){
            saveSettings($k, $v);
        }
        if(saveSettings($k, $v) !== false){$DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','修改了首页配置', NOW(), '".$ip."', '".$city."')");exit('{"code":0,"msg":"设置保存成功！"}');
        }else{ exit('{"code":-1,"msg":"修改设置失败['.$DB->error().']"}');}
    break;
    case 'adminfk':
        if(!$isLogin)exit('{"code":0,"msg":"未登录"}');
        $sub = addslashes($_POST['sub']);
        $msg = addslashes($_POST['msg']);
        $qq = addslashes($_POST['qq']);
        if (conf('Turnstile_Open') == 1) {
            $token = isset($_POST['token'])?$_POST['token']:null;
        }
        if ($sub=='' || $msg=='' || $qq=='') {
            exit('{"code":0,"msg":"请确保每项都不为空"}');
        }
        if (conf('Turnstile_Open') == 1 && $token=='') {
            exit('{"code":0,"msg":"请先完成人机验证"}');
        }
        if (conf('Turnstile_Open') == 1) {
            $ip = $Gets->ip();
            $secret = conf('Turnstile_Secret');
            $resp = get_curl('https://challenges.cloudflare.com/turnstile/v0/siteverify', http_build_query(array('secret'=>$secret,'response'=>$token,'remoteip'=>$ip)));
            $json = json_decode($resp, true);
            if (!$json || empty($json['success'])) {
                exit('{"code":0,"msg":"人机验证失败，请重试"}');
            }
        }
        $sql = "INSERT INTO `nteam_fk` (`sub`, `msg`, `qq`, `time`) VALUES ('{$sub}', '{$msg}', '{$qq}', NOW())";
        $res = $DB->exec($sql);
        if($res){
            $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','提交了一个问题反馈', NOW(), '".$ip."', '".$city."')");
            exit('{"code":1,"msg":"提交反馈成功！"}');
        }else{
            exit('{"code":0,"msg":"提交反馈失败！"}');
        }
    break;
    case 'AddAdmin':
        if($adminData['adminRank']!=1){exit('{"code":-1,"msg":"您的账号没有权限使用此功能！"}');}
        $data['adminUser'] = addslashes($_POST['adminUser']);
        $data['adminPwd'] = md5($_POST['adminPwd']);
        $data['adminQq'] = $_POST['adminQq'];
        $data['adminRank'] = $_POST['adminRank'];
        if($data['adminUser']==NULL || $data['adminPwd']==NULL || $data['adminQq']==NULL || $data['adminRank']==NULL){
            exit('{"code":-1,"msg":"保存错误,请确保每项都不为空!"}');
        } else {
        $AdminData = $Admin->getAdminName($data['adminUser']);
        if(!empty($AdminData))exit('{"code":-1,"msg":"账号已存在"}');

        if(!$Admin->AddAdmin($data))exit('{"code":-1,"msg":"添加管理员失败！"}');
        $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','添加了一位账号为".$data['adminUser']."管理员', NOW(), '".$ip."', '".$city."')");
        exit('{"code":0,"msg":"添加管理员成功！"}');
        }
    break;
    case 'EditAdmin':
        if($adminData['adminRank']!=1){exit('{"code":-1,"msg":"您的账号没有权限使用此功能！"}');}
        $ids=$Admin->getAdminName($_POST['adminUser']);
        $id=$ids['id'];
        $rows=$DB->getRow("select * from nteam_admin where id='$id' limit 1");
        if($adminData['id']!='1'){
            exit('{"code":-1,"msg":"去你的 别人信息是你想改就改的？<br>要改自己的请到修改密码页面修改"}');
        }
        if(!$rows)exit('{"code":-1,"msg":"当前管理员不存在！"}');
        $adminUser = addslashes($_POST['adminUser']);
        $adminQq = addslashes($_POST['adminQq']);
        $adminRank = addslashes($_POST['adminRank']);
        $adminData = $Admin->getAdmin($adminUser);
        if($_POST['adminUser']==NULL || $_POST['adminQq']==NULL || $_POST['adminRank']==NULL){
            exit('{"code":-1,"msg":"保存错误,请确保每项都不为空!"}');
        } else {
            if($adminRank>2){
                exit('{"code":-1,"msg":"数据非法！"}');
            }
            $sql = "UPDATE `nteam_admin` SET `adminUser` = '$adminUser',`adminQq` = '$adminQq',`adminRank` = '$adminRank' WHERE `id` = '$id'";
            if(!empty($_POST['adminPwd'])){
                $adminPwd = md5($_POST['adminPwd']);
                if($adminPwd == $adminData['adminPwd'])exit('{"code":-1,"msg":"与原密码相同！"}');
                $sql = "UPDATE `nteam_admin` SET `adminPwd` = '$adminPwd',`adminUser` = '$adminUser',`adminQq` = '$adminQq',`adminRank` = '$adminRank' WHERE `id` = '$id'";
            }
            $admininfo = $DB->exec($sql);
            if(!$admininfo)exit('{"code":-1,"msg":"修改失败。"}');
            $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','修改账号为".$adminUser."的管理员信息', NOW(), '".$ip."', '".$city."')");
            exit('{"code":0,"msg":"修改成功！！"}');
        }
    break;
    case 'DelAdmin':
        if($adminData['adminRank']!=1){exit('{"code":-1,"msg":"您的账号没有权限使用此功能！"}');}
        $id=$_POST['id'];
        $rows=$DB->getRow("select * from nteam_admin where id='$id' limit 1");
        if(!$rows)exit('{"code":-1,"msg":"当前管理员不存在"}');
        if(!$Admin->delAdmin($id)){exit('{"code":-1,"msg":"删除管理员失败！"}');}else{
        $DB->query("insert into `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) values ('" . $_SESSION['adminUser'] . "','删除了账号为".$AdminData['adminUser']."的管理员', NOW(), '".$ip."', '".$city."')");
        exit('{"code":0,"msg":"删除管理员成功！"}');}
        break;
    case 'save_announcement':
        if(!$isLogin)exit('{"code":0,"msg":"未登录"}');
        
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $title = addslashes($_POST['title']);
        $content = addslashes($_POST['content']);
        $type = intval($_POST['type']);
        $status = intval($_POST['status']);
        $pinned = isset($_POST['pinned']) ? 1 : 0;
        
        if($title == '' || $content == '') {
            exit('{"code":0,"msg":"請確保標題和內容都不為空"}');
        }
        
        if($id) { // 編輯
            $sql = "UPDATE nteam_announcements SET title='$title',content='$content',type='$type',status='$status',pinned='$pinned',update_time=NOW() WHERE id='$id'";
            if($DB->exec($sql)) {
                $DB->query("INSERT INTO `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) VALUES ('" . $_SESSION['adminUser'] . "','編輯了標題為".$title."的公告', NOW(), '".$ip."', '".$city."')");
                exit('{"code":1,"msg":"編輯公告成功"}');
            } else {
                exit('{"code":0,"msg":"編輯公告失敗"}');
            }
        } else { // 新增
            $sql = "INSERT INTO nteam_announcements (title,content,type,status,pinned,creator_id,create_time,update_time) VALUES ('$title','$content','$type','$status','$pinned','".$adminData['id']."',NOW(),NOW())";
            if($DB->exec($sql)) {
                $DB->query("INSERT INTO `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) VALUES ('" . $_SESSION['adminUser'] . "','發布了標題為".$title."的公告', NOW(), '".$ip."', '".$city."')");
                exit('{"code":1,"msg":"發布公告成功"}');
            } else {
                exit('{"code":0,"msg":"發布公告失敗"}');
            }
        }
        break;
    case 'delete_announcement':
        if(!$isLogin)exit('{"code":0,"msg":"未登录"}');
        
        $id = intval($_POST['id']);
        $announcement = $DB->getRow("SELECT title FROM nteam_announcements WHERE id='$id'");
        if(!$announcement) exit('{"code":0,"msg":"公告不存在"}');
        
        $sql = "DELETE FROM nteam_announcements WHERE id='$id'";
        if($DB->exec($sql)) {
            $DB->query("INSERT INTO `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) VALUES ('" . $_SESSION['adminUser'] . "','刪除了標題為".$announcement['title']."的公告', NOW(), '".$ip."', '".$city."')");
            exit('{"code":1,"msg":"刪除公告成功"}');
        } else {
            exit('{"code":0,"msg":"刪除公告失敗"}');
        }
        break;
    case 'toggle_announcement_pin':
        if(!$isLogin)exit('{"code":0,"msg":"未登录"}');
        
        $id = intval($_POST['id']);
        $announcement = $DB->getRow("SELECT title,pinned FROM nteam_announcements WHERE id='$id'");
        if(!$announcement) exit('{"code":0,"msg":"公告不存在"}');
        
        $pinned = $announcement['pinned'] ? 0 : 1;
        $sql = "UPDATE nteam_announcements SET pinned='$pinned' WHERE id='$id'";
        if($DB->exec($sql)) {
            $action = $pinned ? '置頂' : '取消置頂';
            $DB->query("INSERT INTO `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) VALUES ('" . $_SESSION['adminUser'] . "','".$action."了標題為".$announcement['title']."的公告', NOW(), '".$ip."', '".$city."')");
            exit('{"code":1,"msg":"操作成功"}');
        } else {
            exit('{"code":0,"msg":"操作失敗"}');
        }
        exit('{"code":0,"msg":"操作失败"}');
        }
        break;
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
            exit('{"code":0,"msg":"該分類下還有'.$blog_count.'篇文章，請先移動或刪除這些文章"}');
        }
        
        $sql = "DELETE FROM nteam_blog_categories WHERE id='$id'";
        if($DB->exec($sql)) {
            $DB->query("INSERT INTO `nteam_log` (`adminUser`,`type`,`data`,`ip`,`city`) VALUES ('" . $_SESSION['adminUser'] . "','刪除了博客分類：".$category['name']."', NOW(), '".$ip."', '".$city."')");
            exit('{"code":1,"msg":"刪除分類成功"}');
        } else {
            exit('{"code":0,"msg":"刪除分類失敗"}');
        }
        break;
    case 'adminfk':
        if(isset($_SESSION['Tg_submit']) && $_SESSION['Tg_submit']>time()-300){
            exit('{"code":-1,"msg":"请勿频繁提交，要再次申请请等待5分钟!"}');
        }
        $sub = addslashes($_POST['sub']);
        $msg = addslashes($_POST['msg']);
        $qq = addslashes($_POST['qq']);
        $token = addslashes($_POST['token']);
        $yun_url= "https://www.nanyinet.cn/Ajax.php?act=admintg&sub=".$sub."&msg=".$msg."&qq=".$qq."&token=".$token;
        $yun_get= file_get_contents($yun_url);
        $yun_json= json_decode($yun_get,true);
        if ($yun_json['code'] == 0){
            $_SESSION['Tg_submit']=time();
            exit('{"code":1,"msg":"'.$yun_json['msg'].'"}');
        }else{
            exit('{"code":0,"msg":"'.$yun_json['msg'].'"}');
        }
        break;
    default:
        exit('{"code":-4,"msg":"No Act"}');
    break;
}