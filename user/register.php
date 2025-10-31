<?php
include("../Common/Core_brain.php");
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <title>用户注册 - <?php echo conf('Name');?></title>
    <link rel="icon" href="../favicon.ico" type="image/ico">
    <meta content="<?php echo conf('Descriptison');?>" name="descriptison">
    <meta content="<?php echo conf('Keywords');?>" name="keywords">
    <meta name="author" content="<?php echo conf('Name');?>">
    <link href="../assets/admin/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/admin/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="../assets/admin/css/style.min.css" rel="stylesheet">
    <style>
        .register-box {
            max-width: 500px;
            margin: 5% auto;
            position: relative;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
        }
        .register-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .register-title h2 {
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
        .register-box .input-group-addon {
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
    <div class="register-box">
        <div class="register-title">
            <h2>用户注册</h2>
        </div>
        <form action="javascript:void(0);" method="post" onsubmit="return register()">
            <div class="form-group has-feedback">
                <div class="input-group">
                    <span class="input-group-addon"><i class="mdi mdi-account"></i></span>
                    <input type="text" class="form-control" name="username" placeholder="请输入用户名(3-16位)" required>
                </div>
            </div>
            <div class="form-group has-feedback">
                <div class="input-group">
                    <span class="input-group-addon"><i class="mdi mdi-email"></i></span>
                    <input type="email" class="form-control" name="email" placeholder="请输入邮箱" required>
                </div>
            </div>
            <div class="form-group has-feedback">
                <div class="input-group">
                    <span class="input-group-addon"><i class="mdi mdi-lock"></i></span>
                    <input type="password" class="form-control" name="password" placeholder="请输入密码(6-16位)" required>
                </div>
            </div>
            <div class="form-group has-feedback">
                <div class="input-group">
                    <span class="input-group-addon"><i class="mdi mdi-lock-outline"></i></span>
                    <input type="password" class="form-control" name="repassword" placeholder="请再次输入密码" required>
                </div>
            </div>
            <?php if(conf('Turnstile_Open') == 1){?>
            <div class="form-group">
                <div class="cf-turnstile" data-sitekey="<?php echo conf('Turnstile_SiteKey');?>"></div>
            </div>
            <?php }?>
            <div class="form-group">
                <button class="btn btn-primary btn-block" type="submit">立即注册</button>
            </div>
            <div class="form-group">
                <a href="login.php" class="btn btn-info btn-block">返回登录</a>
            </div>
            <div class="form-group" style="text-align: center;">
                <a href="../" class="text-muted">返回首页</a>
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
function register(){
    var username = $("input[name='username']").val();
    var email = $("input[name='email']").val();
    var password = $("input[name='password']").val();
    var repassword = $("input[name='repassword']").val();
    if(password != repassword){
        layer.msg('两次输入的密码不一致！', {icon: 2});
        return false;
    }
    var data = {
        username: username,
        email: email,
        password: password
    };
    <?php if(conf('Turnstile_Open') == 1){?>
    var token = turnstile.getResponse();
    if(!token){
        layer.msg('请完成人机验证', {icon: 2});
        return false;
    }
    data.token = token;
    <?php }?>
    var load = layer.load();
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=register',
        data: data,
        dataType: 'json',
        success: function(data) {
            layer.close(load);
            if(data.code == 1){
                layer.msg(data.msg, {icon: 1});
                setTimeout(function () {
                    window.location.href = 'login.php';
                }, 1000);
            }else{
                layer.msg(data.msg, {icon: 2});
                <?php if(conf('Turnstile_Open') == 1){?>
                turnstile.reset();
                <?php }?>
            }
        },
        error: function(data) {
            layer.close(load);
            layer.msg('服务器错误', {icon: 2});
            <?php if(conf('Turnstile_Open') == 1){?>
            turnstile.reset();
            <?php }?>
        }
    });
    return false;
}
</script>
</body>
</html>