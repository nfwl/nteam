<?php
include("./Common/Core_brain.php");
$act=isset($_GET['act'])?daddslashes($_GET['act']):null;

switch ($act) {
	case 'contact':
		@header('Content-Type: application/json; charset=UTF-8');
		$name = daddslashes(htmlspecialchars(strip_tags(trim($_POST['name']))));
		$email = daddslashes(htmlspecialchars(strip_tags(trim($_POST['email']))));
		$subject = daddslashes(htmlspecialchars(strip_tags(trim($_POST['subject']))));
		$message = daddslashes(htmlspecialchars(strip_tags(trim($_POST['message']))));

		if ($name=='' || $email=='' || $subject=='' || $message=='') {
			exit('{"code":-1,"msg":"请确保每项都不为空"}');
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			exit('{"code":-1,"msg":"请输入正确的邮箱地址"}');
		}

	    if(conf('Mail_Name') == '' || conf('Mail_Pwd') == ''){
			exit('{"code":-1,"msg":"请先配置邮箱信息"}');
		}

		$admins = $DB->query("SELECT * FROM nteam_admin WHERE id=1");
		$admin = $admins->fetch();
		if(!$admin) {
			exit('{"code":-1,"msg":"系统错误：未找到管理员信息"}');
		}
		$admin_email = $admin['adminQq'].'@qq.com';

		$sub = '收到新的网站留言';
		$msg = get_email_template(
			'新留言通知',
			"亲爱的管理员：\n\n".
			"网站收到了新的留言消息！以下是详细信息：\n\n".
			"【留言者信息】\n".
			"• 姓名：{$name}\n".
			"• 邮箱：{$email}\n\n".
			"【留言内容】\n".
			"• 主题：{$subject}\n".
			"• 内容：\n{$message}\n\n".
			"【系统信息】\n".
			"• 提交时间：" . date('Y-m-d H:i:s') . "\n".
			"• IP地址：" . $Gets->ip() . "\n\n".
			"如需回复，请直接通过邮箱与留言者联系。",
			array(
				'text' => '查看所有留言',
				'url' => 'http://' . conf('Url') . '/Admin/leave_list.php'
			)
		);

		$result = send_mail($admin_email, $sub, $msg);
		if($result === true){
			$sql = "INSERT INTO `nteam_leave_messages` (`name`,`email`,`subject`,`message`,`intime`) VALUES (?,?,?,?,NOW())";
			$stmt = $DB->prepare($sql);
			if($stmt->execute(array($name, $email, $subject, $message))) {
				exit('{"code":0,"msg":"留言发送成功"}');
			} else {
				exit('{"code":-1,"msg":"留言保存失败"}');
			}
		}else{
			file_put_contents('mail.log', $result);
			exit('{"code":-1,"msg":"邮件发送失败：' . htmlspecialchars($result) . '"}');
		}
		break;
	case 'subscribe':
		@header('Content-Type: application/json; charset=UTF-8');
		$email = daddslashes(htmlspecialchars(strip_tags(trim($_POST['email']))));
		
		if ($email == '') {
			exit('{"code":-1,"msg":"请输入邮箱地址"}');
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			exit('{"code":-1,"msg":"请输入正确的邮箱地址"}');
		}

		// 检查邮箱是否已订阅
		$stmt = $DB->prepare("SELECT COUNT(*) FROM nteam_subscribe WHERE email = ?");
		$stmt->execute(array($email));
		if($stmt->fetchColumn() > 0) {
			exit('{"code":-1,"msg":"该邮箱已经订阅过了"}');
		}

		if (conf('Turnstile_Open') == 1) {
			$token = isset($_POST['token']) ? $_POST['token'] : null;
			if ($token == '') {
				exit('{"code":-1,"msg":"请先完成人机验证"}');
			}

			$ip = $Gets->ip();
			$secret = conf('Turnstile_Secret');
			$resp = get_curl('https://challenges.cloudflare.com/turnstile/v0/siteverify', http_build_query(array(
				'secret' => $secret,
				'response' => $token,
				'remoteip' => $ip
			)));
			$json = json_decode($resp, true);
			if (!$json || empty($json['success'])) {
				exit('{"code":-1,"msg":"人机验证失败，请重试"}');
			}
		}

		if(conf('Mail_Name') == '' || conf('Mail_Pwd') == '') {
			exit('{"code":-1,"msg":"系统邮箱未配置，请联系管理员"}');
		}

		// 保存订阅信息
		$stmt = $DB->prepare("INSERT INTO nteam_subscribe (email, subscribe_time, ip) VALUES (?, NOW(), ?)");
		if(!$stmt->execute(array($email, $ip))) {
			exit('{"code":-1,"msg":"订阅失败，请稍后重试"}');
		}

		// 发送通知给管理员
		$admin = $DB->query("SELECT * FROM nteam_admin WHERE id=1")->fetch();
		if($admin) {
			$admin_email = $admin['adminQq'].'@qq.com';
			$sub = '新订阅通知';
			$msg = get_email_template(
				'新订阅通知',
				"亲爱的管理员：\n\n".
				"网站收到了新的订阅请求！\n\n".
				"订阅者信息：\n".
				"• 邮箱：" . $email . "\n".
				"• IP地址：" . $ip . "\n".
				"• 订阅时间：" . date('Y-m-d H:i:s') . "\n\n".
				"您可以登录后台管理系统查看所有订阅者信息。"
			);
			send_mail($admin_email, $sub, $msg);
		}

		// 发送确认邮件给订阅者
		$site_name = conf('Name');
		$site_url = 'http://' . conf('Url');
		$sub = '订阅确认 - ' . $site_name;
		$msg = get_email_template(
			'订阅确认',
			"亲爱的订阅者：\n\n".
			"感谢您订阅 {$site_name} 的最新动态！\n\n".
			"我们将会第一时间向您推送：\n".
			"• 网站重要更新通知\n".
			"• 新项目发布信息\n".
			"• 团队活动预告\n".
			"• 其他重要公告\n\n".
			"如果这不是您的操作，您可以直接忽略本邮件。\n\n".
			"再次感谢您的关注与支持！",
			array(
				'text' => '访问我们的网站',
				'url' => $site_url
			)
		);
		$result = send_mail($email, $sub, $msg);
		
		if ($result === true) {
			exit('{"code":0,"msg":"订阅成功！确认邮件已发送到您的邮箱"}');
		} else {
			// 订阅成功但发送确认邮件失败
			exit('{"code":0,"msg":"订阅成功！但发送确认邮件失败，请检查邮箱是否正确"}');
		}
		break;
	case 'Query_submit':
		@header('Content-Type: application/json; charset=UTF-8');
		$qq = trim($_POST['qq']);
		
		// QQ號格式檢查
		if ($qq == '') {
			exit('{"code":-1,"msg":"请输入要查询的QQ号"}');
		}
		if (!preg_match('/^[1-9][0-9]{4,11}$/', $qq)) {
			exit('{"code":-1,"msg":"请输入正确的QQ号码"}');
		}
		
		// 驗證碼檢查
		if (conf('Turnstile_Open') == 1) {
			$token = isset($_POST['token'])?$_POST['token']:null;
			if ($token == '') {
				exit('{"code":-1,"msg":"请先完成人机验证"}');
			}
			
			$ip = $Gets->ip();
			$secret = conf('Turnstile_Secret');
			$resp = get_curl('https://challenges.cloudflare.com/turnstile/v0/siteverify', http_build_query(array(
				'secret' => $secret,
				'response' => $token,
				'remoteip' => $ip
			)));
			$json = json_decode($resp, true);
			if (!$json || empty($json['success'])) {
				exit('{"code":-1,"msg":"人机验证失败，请重试"}');
			}
		}
		
		// 查詢成員
		$stmt = $DB->prepare("SELECT * FROM nteam_team_member WHERE qq = ? AND is_show = 1 AND Audit_status = 1 LIMIT 1");
		$stmt->execute(array($qq));
		$member = $stmt->fetch();
		
		if(!$member) {
			// 記錄查詢
			$city = $Gets->get_city($ip);
			$DB->prepare("INSERT INTO nteam_log (type,ip,city,data) VALUES (?,?,?,NOW())")
			   ->execute(array("查询QQ：".$qq, $ip, $city));
			   
			exit('{"code":-1,"msg":"未找到该QQ对应的团队成员信息"}');
		}
		
		// 返回成員資訊
		$response = array(
			'code' => 0,
			'msg' => '查询成功',
			'data' => array(
				'name' => $member['name'],
				'qq' => $member['qq'],
				'join_time' => $member['intime'],
				'description' => $member['describe']
			)
		);
		
		// 記錄查詢
		$city = $Gets->get_city($ip);
		$DB->prepare("INSERT INTO nteam_log (type,ip,city,data) VALUES (?,?,?,NOW())")
		   ->execute(array("查询成员：".$member['name'], $ip, $city));
		   
		exit(json_encode($response));
		break;
	case 'Join_submit':
		@header('Content-Type: application/json; charset=UTF-8');
		
		// 輸入驗證
		$name = trim($_POST['name']);
		$qq = trim($_POST['qq']);
		$describe = trim($_POST['describe']);
		
		if ($name == '' || $qq == '' || $describe == '') {
			exit('{"code":-1,"msg":"请填写所有必填项"}');
		}
		
		// QQ號格式檢查
		if (!preg_match('/^[1-9][0-9]{4,11}$/', $qq)) {
			exit('{"code":-1,"msg":"请输入正确的QQ号码"}');
		}
		
		// 昵稱長度檢查
		if (mb_strlen($name, 'UTF-8') < 2 || mb_strlen($name, 'UTF-8') > 20) {
			exit('{"code":-1,"msg":"昵称长度需在2-20个字符之间"}');
		}
		
		// 簡介長度檢查
		if (mb_strlen($describe, 'UTF-8') < 10 || mb_strlen($describe, 'UTF-8') > 200) {
			exit('{"code":-1,"msg":"简介长度需在10-200个字符之间"}');
		}
		
		// 頻率限制
		if(isset($_SESSION['Join_submit']) && $_SESSION['Join_submit'] > time()-300){
			exit('{"code":-1,"msg":"请等待5分钟后再次申请"}');
		}
		
		// 檢查是否已經是成員
		$stmt = $DB->prepare("SELECT id FROM nteam_team_member WHERE qq = ?");
		$stmt->execute(array($qq));
		if($stmt->fetch()) {
			exit('{"code":-1,"msg":"该QQ已经申请过或已是团队成员"}');
		}
		
		// 驗證碼檢查
		if (conf('Turnstile_Open') == 1) {
			$token = isset($_POST['token'])?$_POST['token']:null;
			if ($token == '') {
				exit('{"code":-1,"msg":"请先完成人机验证"}');
			}
			
			$ip = $Gets->ip();
			$secret = conf('Turnstile_Secret');
			$resp = get_curl('https://challenges.cloudflare.com/turnstile/v0/siteverify', http_build_query(array(
				'secret' => $secret,
				'response' => $token,
				'remoteip' => $ip
			)));
			$json = json_decode($resp, true);
			if (!$json || empty($json['success'])) {
				exit('{"code":-1,"msg":"人机验证失败，请重试"}');
			}
		}
		
		// 檢查郵件配置
		if(conf('Mail_Name') == '' || conf('Mail_Pwd') == '') {
			exit('{"code":-1,"msg":"系统邮箱未配置，请联系管理员"}');
		}
		
		// 保存申請
		try {
			$stmt = $DB->prepare("INSERT INTO `nteam_team_member` (`name`, `qq`, `describe`, `is_show`, `Audit_status`, `intime`, `ip`) VALUES (?, ?, ?, 0, 0, NOW(), ?)");
			$stmt->execute(array($name, $qq, $describe, $ip));
			$id = $DB->lastInsertId();
			
			// 發送通知郵件給管理員
			$admin = $DB->query("SELECT * FROM nteam_admin WHERE id=1")->fetch();
			if($admin) {
				$admin_email = $admin['adminQq'].'@qq.com';
				$sub = '新成员申请通知';
				$msg = get_email_template(
					'收到新的成员申请',
					"亲爱的管理员：\n\n".
					"有新用户申请加入团队！以下是申请详情：\n\n".
					"【基本信息】\n".
					"• 昵称：{$name}\n".
					"• QQ号：{$qq}\n\n".
					"【申请简介】\n".
					"{$describe}\n\n".
					"【其他信息】\n".
					"• IP地址：{$ip}\n".
					"• 申请时间：" . date('Y-m-d H:i:s') . "\n\n".
					"请及时登录后台对该申请进行审核。如果长时间未处理，系统将自动发送提醒。",
					array(
						'text' => '立即审核申请',
						'url' => 'http://' . conf('Url') . '/Admin/team_approval.php'
					)
				);
					
				$result = send_mail($admin_email, $sub, $msg);
			}
			
			// 記錄日誌
			$city = $Gets->get_city($ip);
			$DB->prepare("INSERT INTO nteam_log (type,ip,city,data) VALUES (?,?,?,NOW())")
			   ->execute(array("新成员申请：".$name, $ip, $city));
			   
			$_SESSION['Join_submit'] = time();
			exit('{"code":0,"msg":"申请已提交，请等待管理员审核"}');
			
		} catch (Exception $e) {
			error_log($e->getMessage());
			exit('{"code":-1,"msg":"申请提交失败，请稍后重试"}');
		}
		break;
	default:
		exit('{"code":-4,"msg":"No Act"}');
		break;
}
?>