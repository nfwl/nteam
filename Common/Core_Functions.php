<?php
function daddslashes($string, $force = 0, $strip = FALSE) {
	!defined('MAGIC_QUOTES_GPC') && define('MAGIC_QUOTES_GPC', get_magic_quotes_gpc());
	if(!MAGIC_QUOTES_GPC || $force) {
		if(is_array($string)) {
			foreach($string as $key => $val) {
				$string[$key] = daddslashes($val, $force, $strip);
			}
		} else {
			$string = addslashes($strip ? stripslashes($string) : $string);
		}
	}
	return $seed;
}

function get_email_template($title, $content, $button = null) {
    $site_name = conf('Name');
    $site_url = conf('Url');
    
    $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>'.$title.'</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4; font-family: Arial, sans-serif;">
    <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px; margin: 20px auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <tr>
            <td style="padding: 20px; text-align: center; background-color: #5c8af7; border-radius: 8px 8px 0 0;">
                <h1 style="color: #ffffff; margin: 0; font-size: 24px;">'.$site_name.'</h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 30px;">
                <h2 style="color: #333333; margin: 0 0 20px 0;">'.$title.'</h2>
                <div style="color: #666666; line-height: 1.6; margin-bottom: 30px;">
                    '.nl2br(htmlspecialchars($content)).'
                </div>';
    
    if ($button) {
        $html .= '
                <table border="0" cellpadding="0" cellspacing="0" width="100%" style="margin-bottom: 30px;">
                    <tr>
                        <td align="center">
                            <a href="'.$button['url'].'" style="display: inline-block; padding: 12px 30px; background-color: #5c8af7; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">'.$button['text'].'</a>
                        </td>
                    </tr>
                </table>';
    }
    
    $html .= '
            </td>
        </tr>
        <tr>
            <td style="padding: 20px; text-align: center; background-color: #f8f9fa; border-radius: 0 0 8px 8px;">
                <p style="color: #999999; margin: 0; font-size: 12px;">
                    此邮件由系统自动发送，请勿直接回复<br>
                    © '.date('Y').' '.$site_name.' - <a href="http://'.$site_url.'" style="color: #5c8af7; text-decoration: none;">'.$site_url.'</a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>';
    
    return $html;
}
}
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0) {
	$ckey_length = 4;
	$key = md5($key ? $key : ENCRYPT_KEY);
	$keya = md5(substr($key, 0, 16));
	$keyb = md5(substr($key, 16, 16));
	$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : '';
	$cryptkey = $keya.md5($keya.$keyc);
	$key_length = strlen($cryptkey);
	$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
	$string_length = strlen($string);
	$result = '';
	$box = range(0, 255);
	$rndkey = array();
	for($i = 0; $i <= 255; $i++) {
		$rndkey[$i] = ord($cryptkey[$i % $key_length]);
	}
	for($j = $i = 0; $i < 256; $i++) {
		$j = ($j + $box[$i] + $rndkey[$i]) % 256;
		$tmp = $box[$i];
		$box[$i] = $box[$j];
		$box[$j] = $tmp;
	}
	for($a = $j = $i = 0; $i < $string_length; $i++) {
		$a = ($a + 1) % 256;
		$j = ($j + $box[$a]) % 256;
		$tmp = $box[$a];
		$box[$a] = $box[$j];
		$box[$j] = $tmp;
		$result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
	}
	if($operation == 'DECODE') {
		if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
			return substr($result, 26);
		} else {
			return '';
		}
	} else {
		return $keyc.str_replace('=', '', base64_encode($result));
	}
}
function get_curl($url, $post=0, $referer=0, $cookie=0, $header=0, $ua=0, $nobaody=0)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
	$httpheader[] = "Accept: */*";
	$httpheader[] = "Accept-Encoding: gzip,deflate,sdch";
	$httpheader[] = "Accept-Language: zh-CN,zh;q=0.8";
	$httpheader[] = "Connection: close";
	curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
	if ($post) {
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	}
	if ($header) {
		curl_setopt($ch, CURLOPT_HEADER, true);
	}
	if ($cookie) {
		curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	}
	if($referer){
		if($referer==1){
			curl_setopt($ch, CURLOPT_REFERER, 'http://m.qzone.com/infocenter?g_f=');
		}else{
			curl_setopt($ch, CURLOPT_REFERER, $referer);
		}
	}
	if ($ua) {
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
	}
	else {
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Linux; U; Android 4.0.4; es-mx; HTC_One_X Build/IMM76D) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0");
	}
	if ($nobaody) {
		curl_setopt($ch, CURLOPT_NOBODY, 1);
	}
	curl_setopt($ch, CURLOPT_ENCODING, "gzip");
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$ret = curl_exec($ch);
	curl_close($ch);
	return $ret;
}
function random($length, $numeric = 0) {
	$seed = base_convert(md5(microtime().$_SERVER['DOCUMENT_ROOT']), 16, $numeric ? 10 : 35);
	$seed = $numeric ? (str_replace('0', '', $seed).'012340567890') : ($seed.'zZ'.strtoupper($seed));
	$hash = '';
	$max = strlen($seed) - 1;
	for($i = 0; $i < $length; $i++) {
		$hash .= $seed{mt_rand(0, $max)};
	}
	return $hash;
}
function send_mail($to, $sub, $msg) {
	global $DB;
	$Host = conf('Mail_Smtp');
	$Port = conf('Mail_Port');
	$Username = conf('Mail_Name');
	$Password = conf('Mail_Pwd');
	$From = conf('Mail_Name');
	$Nickname = conf('Name');
	$SMTPAuth = 1;
	$SSL = 1;
	$mail = new \lib\mail\SMTP($Host , $Port , $SMTPAuth , $Username , $Password , $SSL);
	$mail->att = array();
	if($mail->send($to , $From , $sub , $msg, $Nickname)) {
		return true;
	} else {
		return $mail->log;
	}
}
function conf($name) {
	global $DB;
	$confs=$DB->query("SELECT * FROM nteam_config WHERE id=1");
	while($conf = $confs->fetch()){
		return $conf[$name];
	}
}
function conf_index($name) {
	global $DB;
	$confs=$DB->query("SELECT * FROM nteam_config_theme WHERE id=1");
	while($conf = $confs->fetch()){
		return $conf[$name];
	}
}
function saveSetting($k, $v) {
	global $DB;
	return $DB->exec("UPDATE nteam_config SET $k='{$v}' WHERE `id`=1");
}
function saveSettings($k, $v) {
	global $DB;
	return $DB->exec("UPDATE nteam_config_theme SET $k='{$v}' WHERE `id`=1");
}
?>