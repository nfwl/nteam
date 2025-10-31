<?php
namespace lib;

class Security {
    /**
     * 使用 HMAC 加密密碼
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * 驗證密碼
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }

    /**
     * XSS 過濾
     */
    public static function escapeHtml($str) {
        return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * SQL 注入防護
     */
    public static function escapeSql($str) {
        if(is_array($str)) {
            foreach($str as $key => $value) {
                $str[$key] = self::escapeSql($value);
            }
        } else {
            $str = addslashes($str);
        }
        return $str;
    }

    /**
     * CSRF Token 生成
     */
    public static function generateCsrfToken() {
        if(empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * CSRF Token 驗證
     */
    public static function validateCsrfToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * 數據庫參數綁定預處理
     */
    public static function bindParams($sql, $params) {
        global $DB;
        $stmt = $DB->prepare($sql);
        foreach($params as $key => $value) {
            $stmt->bindValue(":$key", $value);
        }
        return $stmt;
    }

    /**
     * 檔案上傳安全檢查
     */
    public static function validateUpload($file, $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'], $maxSize = 2097152) {
        if(!isset($file['error']) || is_array($file['error'])) {
            throw new \RuntimeException('無效的檔案參數');
        }

        switch($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                throw new \RuntimeException('沒有檔案被上傳');
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                throw new \RuntimeException('超出檔案大小限制');
            default:
                throw new \RuntimeException('未知錯誤');
        }

        if($file['size'] > $maxSize) {
            throw new \RuntimeException('檔案大小超出限制');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if(!in_array($ext, $allowedTypes)) {
            throw new \RuntimeException('不允許的檔案類型');
        }

        return true;
    }

    /**
     * 日誌記錄
     */
    public static function logActivity($userId, $action, $details = '') {
        global $DB;
        $ip = $_SERVER['REMOTE_ADDR'];
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $time = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO nteam_system_log (user_id, action, details, ip, user_agent, created_at) 
                VALUES (:user_id, :action, :details, :ip, :user_agent, :created_at)";
        
        $params = [
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'created_at' => $time
        ];
        
        self::bindParams($sql, $params)->execute();
    }
}