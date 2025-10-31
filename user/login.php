<?php
include("../Common/Core_brain.php");
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <title>用户登录 - <?php echo conf('Name');?></title>
    <link rel="icon" href="../favicon.ico" type="image/ico">
    <meta content="<?php echo conf('Descriptison');?>" name="descriptison">
    <meta content="<?php echo conf('Keywords');?>" name="keywords">
    <meta name="author" content="<?php echo conf('Name');?>">
    <link href="../assets/admin/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/admin/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="../assets/admin/css/style.min.css" rel="stylesheet">
    <?php if(conf('Turnstile_Open') == 1){?>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    <?php }?>
    <style>
        .login-box {
            max-width: 400px;
            margin: 5% auto;
            position: relative;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .login-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-title h2 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .btn-block {
            padding: 12px;
            font-size: 16px;
        }
        body {
            background: #f5f5f5 url(../assets/img/login-bg.jpg) no-repeat center center;
            background-size: cover;
        }
        .form-control {
            height: auto;
            padding: 12px 15px;
        }
        .has-feedback .form-control-feedback {
            height: 46px;
            line-height: 46px;
            width: 46px;
        }
        .login-box .input-group-addon {
            padding: 6px 12px;
            font-size: 16px;
            color: #666;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
    </style>
</head>
  
<body>
<div class="container">
    <div class="login-box">
        <div class="login-title">
            <h2>用戶登入</h2>
        </div>
        
        <!-- 登入方式選擇 -->
        <div class="login-type-switch mb-4">
            <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                <label class="btn btn-outline-primary active">
                    <input type="radio" name="login_type" value="normal" checked> 普通登入
                </label>
                <label class="btn btn-outline-primary">
                    <input type="radio" name="login_type" value="email"> 郵箱登入
                </label>
                <label class="btn btn-outline-primary">
                    <input type="radio" name="login_type" value="linux"> Linux.do
                </label>
            </div>
        </div>

        <!-- 普通登入表單 -->
        <form id="normalLoginForm" action="javascript:void(0);" method="post" onsubmit="return login('normal')" class="login-form">
            <div class="form-group has-feedback">
                <div class="input-group">
                    <span class="input-group-addon"><i class="mdi mdi-account"></i></span>
                    <input type="text" class="form-control" name="username" placeholder="用戶名" required>
                </div>
            </div>
            <div class="form-group has-feedback">
                <div class="input-group">
                    <span class="input-group-addon"><i class="mdi mdi-lock"></i></span>
                    <input type="password" class="form-control" name="password" placeholder="密碼" required>
                </div>
            </div>
            <?php if(conf('Turnstile_Open') == 1){?>
            <div class="form-group">
                <div class="cf-turnstile" data-sitekey="<?php echo conf('Turnstile_SiteKey');?>"></div>
            </div>
            <?php }?>
            <div class="form-group">
                <button class="btn btn-primary btn-block" type="submit">登入</button>
            </div>
        </form>

        <!-- 郵箱登入表單 -->
        <form id="emailLoginForm" action="javascript:void(0);" method="post" onsubmit="return login('email')" class="login-form" style="display:none;">
            <div class="form-group has-feedback">
                <div class="input-group">
                    <span class="input-group-addon"><i class="mdi mdi-email"></i></span>
                    <input type="email" class="form-control" name="email" placeholder="電子郵箱" required>
                </div>
            </div>
            <div class="form-group has-feedback">
                <div class="input-group">
                    <span class="input-group-addon"><i class="mdi mdi-lock"></i></span>
                    <input type="password" class="form-control" name="password" placeholder="密碼" required>
                </div>
            </div>
            <?php if(conf('Turnstile_Open') == 1){?>
            <div class="form-group">
                <div class="cf-turnstile" data-sitekey="<?php echo conf('Turnstile_SiteKey');?>"></div>
            </div>
            <?php }?>
            <div class="form-group">
                <button class="btn btn-primary btn-block" type="submit">郵箱登入</button>
            </div>
        </form>

        <!-- Linux.do登入表單 -->
        <form id="linuxLoginForm" action="javascript:void(0);" method="post" onsubmit="return login('linux')" class="login-form" style="display:none;">
            <div class="form-group has-feedback">
                <div class="input-group">
                    <span class="input-group-addon"><i class="mdi mdi-console"></i></span>
                    <input type="text" class="form-control" name="linux_username" placeholder="Linux.do 用戶名" required>
                </div>
            </div>
            <div class="form-group has-feedback">
                <div class="input-group">
                    <span class="input-group-addon"><i class="mdi mdi-lock"></i></span>
                    <input type="password" class="form-control" name="linux_password" placeholder="Linux.do 密碼" required>
                </div>
            </div>
            <div class="form-group">
                <button class="btn btn-primary btn-block" type="submit">Linux.do 登入</button>
            </div>
        </form>
            <div class="form-group">
                <a href="register.php" class="btn btn-info btn-block">注册新账号</a>
            </div>
            <div class="form-group" style="text-align: center;">
                <a href="../" class="text-muted">返回首页</a> | 
                <a href="forgot.php" class="text-muted">忘记密码</a>
            </div>
        </form>
    </div>
</div>

<script type="text/javascript" src="../assets/admin/js/jquery.min.js"></script>
<script type="text/javascript" src="../assets/admin/js/bootstrap.min.js"></script>
<script src="../assets/layer/layer.js"></script>
<?php if(conf('Turnstile_Open') == 1){?>
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php }?>
<script>
$(document).ready(function() {
    // 處理登入方式切換
    $('input[name="login_type"]').change(function() {
        $('.login-form').hide();
        var type = $(this).val();
        if(type === 'normal') {
            $('#normalLoginForm').show();
        } else if(type === 'email') {
            $('#emailLoginForm').show();
        } else if(type === 'linux') {
            $('#linuxLoginForm').show();
        }
    });
});

function login(type) {
    var data = {};
    var url = 'ajax.php?act=login';
    
    // 取得表單數據
    if(type === 'normal') {
        data.type = 'normal';
        data.username = $("input[name='username']").val();
        data.password = $("input[name='password']").val();
        if(!data.username || !data.password) {
            layer.msg('請輸入用戶名和密碼', {icon: 2});
            return false;
        }
    } else if(type === 'email') {
        data.type = 'email';
        data.email = $("input[name='email']").val();
        data.password = $("#emailLoginForm input[name='password']").val();
        if(!data.email || !data.password) {
            layer.msg('請輸入郵箱和密碼', {icon: 2});
            return false;
        }
        if(!isValidEmail(data.email)) {
            layer.msg('請輸入有效的郵箱地址', {icon: 2});
            return false;
        }
    } else if(type === 'linux') {
        data.type = 'linux';
        data.username = $("input[name='linux_username']").val();
        data.password = $("input[name='linux_password']").val();
        if(!data.username || !data.password) {
            layer.msg('請輸入 Linux.do 用戶名和密碼', {icon: 2});
            return false;
        }
    }
    
    // 檢查 Turnstile 驗證
    <?php if(conf('Turnstile_Open') == 1) { ?>
    var turnstileResponse = '';
    if(type === 'normal') {
        turnstileResponse = $("#normalLoginForm input[name='cf-turnstile-response']").val();
    } else if(type === 'email') {
        turnstileResponse = $("#emailLoginForm input[name='cf-turnstile-response']").val();
    } else if(type === 'linux') {
        turnstileResponse = $("#linuxLoginForm input[name='cf-turnstile-response']").val();
    }
    
    if(!turnstileResponse) {
        layer.msg('請完成人機驗證', {icon: 2});
        return false;
    }
    data.token = turnstileResponse;
    <?php } ?>
    
    var load = layer.load();
    
    if(type === 'linux') {
        // Linux.do 登入流程
        $.ajax({
            type: 'POST',
            url: 'https://linux.do/api/auth',
            data: {
                username: data.username,
                password: data.password
            },
            dataType: 'json',
            success: function(response) {
                if(response.success) {
                    // Linux.do 驗證成功，繼續本地登入
                    data.linux_token = response.token;
                    sendLoginRequest(data, load);
                } else {
                    layer.close(load);
                    layer.msg('Linux.do 驗證失敗', {icon: 2});
                    resetTurnstile();
                }
            },
            error: function() {
                layer.close(load);
                layer.msg('連接 Linux.do 失敗', {icon: 2});
                resetTurnstile();
            }
        });
    } else {
        // 一般登入/郵箱登入
        sendLoginRequest(data, load);
    }
    
    return false;
}

// 發送登入請求到本地伺服器
function sendLoginRequest(data, load) {
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=login',
        data: data,
        dataType: 'json',
        success: function(res) {
            layer.close(load);
            if(res.code == 1) {
                layer.msg('登入成功', {icon: 1});
                setTimeout(function() {
                    window.location.href = './';
                }, 1000);
            } else {
                layer.msg(res.msg, {icon: 2});
                resetTurnstile();
            }
        },
        error: function() {
            layer.close(load);
            layer.msg('伺服器錯誤', {icon: 2});
            resetTurnstile();
        }
    });
}

// 重置 Turnstile 驗證
function resetTurnstile() {
    <?php if(conf('Turnstile_Open') == 1) { ?>
    if(typeof turnstile !== 'undefined') {
        try {
            turnstile.reset();
        } catch(e) {}
    }
    <?php } ?>
}

// 驗證郵箱格式
function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}
</script>
</body>
</html>