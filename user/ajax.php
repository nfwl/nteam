<?php
include("../Common/Core_brain.php");

@header('Content-Type: application/json; charset=UTF-8');

// 設置錯誤處理
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

function verify_turnstile($token) {
    if(!$token) return false;
    
    $secret = conf('Turnstile_Secret');
    $resp = get_curl('https://challenges.cloudflare.com/turnstile/v0/siteverify', http_build_query([
        'secret' => $secret,
        'response' => $token,
        'remoteip' => $Gets->ip()
    ]));
    
    $data = json_decode($resp, true);
    return isset($data['success']) && $data['success'] === true;
}

$act = isset($_GET['act']) ? daddslashes($_GET['act']) : null;

switch($act) {
    case 'linux_login':
        $username = isset($_POST['username']) ? daddslashes($_POST['username']) : null;
        $token = isset($_POST['token']) ? daddslashes($_POST['token']) : null;
        
        if(!$username || !$token) {
            exit(json_encode(['code' => -1, 'msg' => '參數不完整']));
        }
        
        // 驗證 linux.do 返回的 token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://linux.do/api/verify_token");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'token' => $token
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        
        $response = json_decode($result, true);
        if(!$response || !$response['success']) {
            exit(json_encode(['code' => -1, 'msg' => 'Linux驗證失敗']));
        }
        
        // 檢查用戶是否已存在
        $user = $DB->getRow("SELECT * FROM nteam_users WHERE username=:username LIMIT 1", 
            [':username' => $username]
        );
        
        if(!$user) {
            // 如果用戶不存在，創建新用戶
            $stmt = $DB->prepare("INSERT INTO nteam_users (username, reg_time, reg_ip, status) 
                                VALUES (:username, NOW(), :ip, 1)");
            if(!$stmt->execute([
                ':username' => $username,
                ':ip' => $Gets->ip()
            ])) {
                exit(json_encode(['code' => -1, 'msg' => '創建用戶失敗']));
            }
            $userid = $DB->lastInsertId();
        } else {
            $userid = $user['id'];
        }
        
        // 設置會話
        $_SESSION['userid'] = $userid;
        $_SESSION['username'] = $username;
        
        // 記錄登入日誌
        $city = $Gets->get_city($Gets->ip());
        $DB->exec("INSERT INTO `nteam_log` (`type`,`ip`,`city`,`data`) VALUES ('Linux用戶登入','".$Gets->ip()."','".$city."',NOW())");
        
        exit(json_encode(['code' => 1, 'msg' => '登入成功']));
        break;
    case 'updateProfile':
        if(!isset($_SESSION['userid'])) exit('{"code":-1,"msg":"未登录"}');
        
        $qq = addslashes($_POST['qq']);
        $bio = addslashes($_POST['bio']);
        
        if($qq && !preg_match('/^[1-9][0-9]{4,11}$/', $qq)) {
            exit('{"code":-1,"msg":"请输入正确的QQ号码"}');
        }
        
        $stmt = $DB->prepare("UPDATE nteam_users SET qq=:qq,bio=:bio WHERE id=:id");
        if($stmt->execute([':qq'=>$qq,':bio'=>$bio,':id'=>$_SESSION['userid']])) {
            exit('{"code":1,"msg":"修改成功"}');
        } else {
            exit('{"code":-1,"msg":"修改失败"}');
        }
        break;
        
    case 'sendVerifyEmail':
        if(!isset($_SESSION['userid'])) exit('{"code":-1,"msg":"未登录"}');
        
        $user = $DB->getRow("SELECT * FROM nteam_users WHERE id=:id LIMIT 1", [':id'=>$_SESSION['userid']]);
        if($user['email_verified'] == 1) {
            exit('{"code":-1,"msg":"邮箱已验证"}');
        }
        
        $token = md5($user['id'] . $user['email'] . time() . random(8));
        $expire = date('Y-m-d H:i:s', strtotime('+1 day'));
        
        $stmt = $DB->prepare("UPDATE nteam_users SET verify_token=:token,verify_token_expire=:expire WHERE id=:id");
        if(!$stmt->execute([':token'=>$token,':expire'=>$expire,':id'=>$user['id']])) {
            exit('{"code":-1,"msg":"系统错误"}');
        }
        
        $verify_url = 'http://' . conf('Url') . '/user/verify.php?token=' . $token;
        
        $sub = '邮箱验证 - ' . conf('Name');
        $msg = get_email_template(
            '验证您的邮箱',
            "亲爱的 {$user['username']}：\n\n".
            "请点击下面的按钮验证您的邮箱地址。\n\n".
            "如果这不是您的操作，请忽略此邮件。\n\n".
            "验证链接将在24小时后失效。",
            array(
                'text' => '验证邮箱',
                'url' => $verify_url
            )
        );
        
        if(send_mail($user['email'], $sub, $msg) === true) {
            exit('{"code":1,"msg":"验证邮件已发送，请查收"}');
        } else {
            exit('{"code":-1,"msg":"邮件发送失败"}');
        }
        break;
        
    case 'logout':
        unset($_SESSION['userid']);
        unset($_SESSION['username']);
        exit('{"code":1,"msg":"退出成功"}');
        break;

    case 'uploadAvatar':
        try {
            if(!isset($_SESSION['userid'])) {
                throw new \Exception('未登入');
            }
            
            if(!isset($_FILES['avatar'])) {
                throw new \Exception('請選擇文件');
            }

            // 使用安全類進行檔案驗證
            \lib\Security::validateUpload(
                $_FILES['avatar'],
                ['jpg', 'jpeg', 'png', 'gif'],
                2 * 1024 * 1024 // 2MB
            );
            
            $file = $_FILES['avatar'];
            $dir = '../uploads/avatars/';
            if(!is_dir($dir)) mkdir($dir, 0777, true);
            
            $filename = uniqid() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
            $filepath = $dir . $filename;
            
            if(!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new \Exception('上傳失敗');
            }
            
            $url = '/uploads/avatars/' . $filename;
            
            // 使用參數化查詢更新頭像
            $stmt = \lib\Security::bindParams(
                "UPDATE nteam_users SET avatar=:avatar WHERE id=:id",
                [':avatar' => $url, ':id' => $_SESSION['userid']]
            );
            
            if($stmt->execute()) {
                // 刪除舊頭像
                $user = $DB->getRow("SELECT avatar FROM nteam_users WHERE id=:id", [':id' => $_SESSION['userid']]);
                if($user['avatar'] && file_exists('..'.$user['avatar'])) {
                    unlink('..'.$user['avatar']);
                }
                
                \lib\Security::logActivity(
                    $_SESSION['userid'],
                    'avatar_update',
                    '用戶更新了頭像'
                );
                
                exit(json_encode(['code' => 1, 'msg' => '上傳成功', 'url' => $url]));
            } else {
                unlink($filepath);
                throw new \Exception('保存失敗');
            }
            
        } catch(\Exception $e) {
            exit(json_encode(['code' => -1, 'msg' => $e->getMessage()]));
        }
        
        $dir = '../uploads/avatars/';
        if(!is_dir($dir)) mkdir($dir, 0777, true);
        
        $filename = uniqid() . '.' . $ext;
        if(!move_uploaded_file($file['tmp_name'], $dir . $filename)) {
            exit('{"code":-1,"msg":"上传失败"}');
        }
        
        $url = '/uploads/avatars/' . $filename;
        $stmt = $DB->prepare("UPDATE nteam_users SET avatar=:avatar WHERE id=:id");
        if($stmt->execute([':avatar'=>$url,':id'=>$_SESSION['userid']])) {
            if($user['avatar'] && file_exists('..'.$user['avatar'])) {
                unlink('..'.$user['avatar']);
            }
            exit('{"code":1,"msg":"上传成功","url":"'.$url.'"}');
        } else {
            unlink($dir . $filename);
            exit('{"code":-1,"msg":"保存失败"}');
        }
        break;
        
    case 'removeAvatar':
        if(!isset($_SESSION['userid'])) exit('{"code":-1,"msg":"未登录"}');
        
        $user = $DB->getRow("SELECT * FROM nteam_users WHERE id=:id LIMIT 1", [':id'=>$_SESSION['userid']]);
        if($user['avatar'] && file_exists('..'.$user['avatar'])) {
            unlink('..'.$user['avatar']);
        }
        
        $stmt = $DB->prepare("UPDATE nteam_users SET avatar=NULL WHERE id=:id");
        if($stmt->execute([':id'=>$_SESSION['userid']])) {
            exit('{"code":1,"msg":"删除成功"}');
        } else {
            exit('{"code":-1,"msg":"删除失败"}');
        }
        break;
        
    case 'changePassword':
        if(!isset($_SESSION['userid'])) exit('{"code":-1,"msg":"未登录"}');
        
        $oldpwd = md5($_POST['oldpwd']);
        $newpwd = md5($_POST['newpwd']);
        
        $user = $DB->getRow("SELECT * FROM nteam_users WHERE id=:id LIMIT 1", [':id'=>$_SESSION['userid']]);
        if($oldpwd != $user['password']) exit('{"code":-1,"msg":"原密码错误"}');
        
        $stmt = $DB->prepare("UPDATE nteam_users SET password=:password WHERE id=:id");
        if($stmt->execute([':password'=>$newpwd,':id'=>$_SESSION['userid']])) {
            unset($_SESSION['userid']);
            unset($_SESSION['username']);
            exit('{"code":1,"msg":"修改成功，请重新登录"}');
        } else {
            exit('{"code":-1,"msg":"修改失败"}');
        }
        break;
        
    case 'changeEmail':
        if(!isset($_SESSION['userid'])) exit('{"code":-1,"msg":"未登录"}');
        
        $pwd = md5($_POST['pwd']);
        $email = $_POST['email'];
        
        $user = $DB->getRow("SELECT * FROM nteam_users WHERE id=:id LIMIT 1", [':id'=>$_SESSION['userid']]);
        if($pwd != $user['password']) exit('{"code":-1,"msg":"密码错误"}');
        
        if($DB->getRow("SELECT * FROM nteam_users WHERE email=:email AND id!=:id LIMIT 1", [':email'=>$email,':id'=>$_SESSION['userid']])) {
            exit('{"code":-1,"msg":"该邮箱已被使用"}');
        }
        
        $stmt = $DB->prepare("UPDATE nteam_users SET email=:email,email_verified=0 WHERE id=:id");
        if($stmt->execute([':email'=>$email,':id'=>$_SESSION['userid']])) {
            exit('{"code":1,"msg":"修改成功，请验证新邮箱"}');
        } else {
            exit('{"code":-1,"msg":"修改失败"}');
        }
        break;
        
    case 'deleteAccount':
        if(!isset($_SESSION['userid'])) exit('{"code":-1,"msg":"未登录"}');
        
        $pwd = md5($_POST['pwd']);
        
        $user = $DB->getRow("SELECT * FROM nteam_users WHERE id=:id LIMIT 1", [':id'=>$_SESSION['userid']]);
        if($pwd != $user['password']) exit('{"code":-1,"msg":"密码错误"}');
        
        $stmt = $DB->prepare("UPDATE nteam_users SET status=0 WHERE id=:id");
        if($stmt->execute([':id'=>$_SESSION['userid']])) {
            unset($_SESSION['userid']);
            unset($_SESSION['username']);
            exit('{"code":1,"msg":"账号已注销"}');
        } else {
            exit('{"code":-1,"msg":"操作失败"}');
        }
        break;
        
    case 'saveNotifySettings':
        if(!isset($_SESSION['userid'])) exit('{"code":-1,"msg":"未登录"}');
        
        $notify_email = intval($_POST['notify_email']);
        $notify_update = intval($_POST['notify_update']);
        
        $stmt = $DB->prepare("UPDATE nteam_users SET notify_email=:notify_email,notify_update=:notify_update WHERE id=:id");
        if($stmt->execute([':notify_email'=>$notify_email,':notify_update'=>$notify_update,':id'=>$_SESSION['userid']])) {
            exit('{"code":1,"msg":"保存成功"}');
        } else {
            exit('{"code":-1,"msg":"保存失败"}');
        }
        break;
        
    case 'login':
        try {
            $type = $_POST['type'] ?? 'normal';
            
            // 驗證 Turnstile（如果啟用）
            if(conf('Turnstile_Open') == 1) {
                $token = $_POST['token'] ?? null;
                if(!$token) {
                    throw new \Exception('請完成人機驗證');
                }
                
                $verifyResult = verify_turnstile($token);
                if(!$verifyResult) {
                    throw new \Exception('人機驗證失敗');
                }
            }

            switch($type) {
                case 'normal':
                    $username = \lib\Security::escapeHtml($_POST['username']);
                    $password = $_POST['password'];
                    
                    if(empty($username) || empty($password)) {
                        throw new \Exception('請確保各項不為空');
                    }

                    $stmt = $DB->prepare("SELECT * FROM nteam_users WHERE username = :username LIMIT 1");
                    $stmt->execute([':username' => $username]);
                    $user = $stmt->fetch();
                    
                    if(!$user || !password_verify($password, $user['password'])) {
                        throw new \Exception('用戶名或密碼錯誤');
                    }
                    break;

                case 'email':
                    $email = \lib\Security::escapeHtml($_POST['email']);
                    $password = $_POST['password'];
                    
                    if(empty($email) || empty($password)) {
                        throw new \Exception('請確保各項不為空');
                    }

                    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        throw new \Exception('郵箱格式不正確');
                    }

                    $stmt = $DB->prepare("SELECT * FROM nteam_users WHERE email = :email LIMIT 1");
                    $stmt->execute([':email' => $email]);
                    $user = $stmt->fetch();
                    
                    if(!$user || !password_verify($password, $user['password'])) {
                        throw new \Exception('郵箱或密碼錯誤');
                    }
                    break;

                default:
                    throw new \Exception('不支持的登入方式');
            }

            if($user['status'] != 1) {
                throw new \Exception('該帳號已被禁用');
            }

            $_SESSION['userid'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            // 更新最後登入時間和IP
            $stmt = $DB->prepare("UPDATE nteam_users SET last_login=NOW(), last_ip=:ip WHERE id=:id");
            $stmt->execute([':ip' => $Gets->ip(), ':id' => $user['id']]);

            // 記錄登入日誌
            $city = $Gets->get_city($Gets->ip());
            $DB->exec("INSERT INTO `nteam_log` (`type`,`ip`,`city`,`data`) VALUES ('用戶登入','".$Gets->ip()."','".$city."',NOW())");

            exit(json_encode(['code' => 1, 'msg' => '登入成功']));

        } catch(\Exception $e) {
            exit(json_encode(['code' => -1, 'msg' => $e->getMessage()]));
        }
        break;
        
    case 'register':
        try {
            $username = \lib\Security::escapeHtml($_POST['username']);
            $email = \lib\Security::escapeHtml($_POST['email']);
            $password = $_POST['password'];
            
            if(empty($username) || empty($email) || empty($password)) {
                throw new \Exception('請確保各項不為空');
            }
            
            if(strlen($username) < 3 || strlen($username) > 16) {
                throw new \Exception('用戶名長度必須在3-16位之間');
            }
            
            if(strlen($password) < 6 || strlen($password) > 16) {
                throw new \Exception('密碼長度必須在6-16位之間');
            }
            
            if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('郵箱格式不正確');
            }
            
            // 驗證 Turnstile
            if(conf('Turnstile_Open') == 1) {
                $token = $_POST['token'] ?? null;
                if(!$token) {
                    throw new \Exception('請完成人機驗證');
                }
                
                $verifyResult = verify_turnstile($token);
                if(!$verifyResult) {
                    throw new \Exception('人機驗證失敗');
                }
            }
            
            // 檢查用戶名是否已存在
            $stmt = $DB->prepare("SELECT COUNT(*) FROM nteam_users WHERE username = :username");
            $stmt->execute([':username' => $username]);
            if($stmt->fetchColumn() > 0) {
                throw new \Exception('該用戶名已被註冊');
            }
            
            // 檢查郵箱是否已存在
            $stmt = $DB->prepare("SELECT COUNT(*) FROM nteam_users WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if($stmt->fetchColumn() > 0) {
                throw new \Exception('該郵箱已被註冊');
            }
            
            // 使用安全的密碼雜湊
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // 新增用戶
            $stmt = $DB->prepare("INSERT INTO nteam_users (username, email, password, reg_ip, reg_time, status) 
                                VALUES (:username, :email, :password, :ip, NOW(), 1)");
            
            if(!$stmt->execute([
                ':username' => $username,
                ':email' => $email,
                ':password' => $hashedPassword,
                ':ip' => $Gets->ip()
            ])) {
                throw new \Exception('註冊失敗，請稍後重試');
            }
            
            // 記錄日誌
            $city = $Gets->get_city($Gets->ip());
            $DB->exec("INSERT INTO nteam_log (type, ip, city, data) VALUES ('新用戶註冊', '".$Gets->ip()."', '".$city."', NOW())");
            
            // 發送歡迎郵件
            $sub = '歡迎註冊 ' . conf('Name');
            $msg = get_email_template(
                '歡迎加入我們',
                "親愛的 {$username}：\n\n".
                "感謝您註冊成為 ".conf('Name')." 的新用戶！\n\n".
                "您現在可以：\n".
                "• 瀏覽和參與我們的專案\n".
                "• 提交專案申請\n".
                "• 申請加入開發團隊\n".
                "• 參與社群討論\n\n".
                "如果您有任何問題，歡迎隨時通過網站留言功能與我們聯繫。\n\n".
                "祝您使用愉快！",
                array(
                    'text' => '立即訪問',
                    'url' => 'http://' . conf('Url')
                )
            );
            send_mail($email, $sub, $msg);
            
            exit(json_encode(['code' => 1, 'msg' => '註冊成功！']));
            
        } catch(\Exception $e) {
            exit(json_encode(['code' => -1, 'msg' => $e->getMessage()]));
        }
        break;
        
    default:
        exit('{"code":-4,"msg":"No Act"}');
}
?>