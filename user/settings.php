<?php
include("../Common/Core_brain.php");
if(!isset($_SESSION['userid'])) {
    header("Location: ./login.php");
    exit;
}

$user = $DB->getRow("SELECT * FROM nteam_users WHERE id=:id LIMIT 1", [':id'=>$_SESSION['userid']]);
if(!$user) {
    header("Location: ./login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <title>账号设置 - <?php echo conf('Name');?></title>
    <link rel="icon" href="../favicon.ico" type="image/ico">
    <meta content="<?php echo conf('Descriptison');?>" name="descriptison">
    <meta content="<?php echo conf('Keywords');?>" name="keywords">
    <meta name="author" content="<?php echo conf('Name');?>">
    <link href="../assets/admin/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/admin/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="../assets/admin/css/style.min.css" rel="stylesheet">
    <link href="../assets/admin/css/animate.css" rel="stylesheet">
    <style>
        .settings-box {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
        }
        .avatar-upload {
            text-align: center;
            margin: 20px 0;
        }
        .avatar-upload img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            margin-bottom: 10px;
            border: 3px solid #fff;
            box-shadow: 0 0 10px rgba(0,0,0,.1);
        }
        .security-item {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }
        .security-item:last-child {
            border-bottom: none;
        }
    </style>
</head>

<body>
<div class="container">
    <div class="row" style="margin-top: 30px;">
        <div class="col-md-3">
            <div class="settings-box">
                <div class="text-center">
                    <img src="<?php echo $user['avatar'] ? $user['avatar'] : '../assets/img/default-avatar.jpg'; ?>" alt="头像" class="img-circle" style="width: 80px; height: 80px;">
                    <h4 style="margin-top: 10px;"><?php echo htmlspecialchars($user['username']); ?></h4>
                </div>
                <div class="list-group">
                    <a href="profile.php" class="list-group-item"><i class="mdi mdi-account"></i> 个人资料</a>
                    <a href="projects.php" class="list-group-item"><i class="mdi mdi-folder"></i> 我的项目</a>
                    <a href="settings.php" class="list-group-item active"><i class="mdi mdi-settings"></i> 账号设置</a>
                    <a href="javascript:void(0)" onclick="logout()" class="list-group-item text-danger"><i class="mdi mdi-logout"></i> 退出登录</a>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <!-- 头像设置 -->
            <div class="settings-box">
                <h4><i class="mdi mdi-image"></i> 头像设置</h4>
                <hr>
                <div class="avatar-upload">
                    <img src="<?php echo $user['avatar'] ? $user['avatar'] : '../assets/img/default-avatar.jpg'; ?>" id="avatar-preview" alt="头像">
                    <div style="margin-top: 15px;">
                        <input type="file" id="avatar-upload" style="display: none;" accept="image/*">
                        <button class="btn btn-primary" onclick="$('#avatar-upload').click()">上传新头像</button>
                        <?php if($user['avatar']) { ?>
                        <button class="btn btn-default" onclick="removeAvatar()">删除头像</button>
                        <?php } ?>
                    </div>
                    <p class="help-block">支持 jpg、png、gif 格式，文件大小不超过 2MB</p>
                </div>
            </div>

            <!-- 安全设置 -->
            <div class="settings-box">
                <h4><i class="mdi mdi-security"></i> 安全设置</h4>
                <hr>
                <div class="security-item">
                    <div class="row">
                        <div class="col-sm-8">
                            <h5>登录密码</h5>
                            <p class="text-muted">建议您定期更换密码，设置一个安全性高的密码</p>
                        </div>
                        <div class="col-sm-4 text-right">
                            <button class="btn btn-primary" onclick="changePassword()">修改密码</button>
                        </div>
                    </div>
                </div>
                <div class="security-item">
                    <div class="row">
                        <div class="col-sm-8">
                            <h5>邮箱绑定</h5>
                            <p class="text-muted">已绑定邮箱：<?php echo htmlspecialchars($user['email']); ?></p>
                        </div>
                        <div class="col-sm-4 text-right">
                            <button class="btn btn-info" onclick="changeEmail()">修改邮箱</button>
                        </div>
                    </div>
                </div>
                <div class="security-item">
                    <div class="row">
                        <div class="col-sm-8">
                            <h5>账号注销</h5>
                            <p class="text-muted">注销后，您的账号将被永久删除</p>
                        </div>
                        <div class="col-sm-4 text-right">
                            <button class="btn btn-danger" onclick="deleteAccount()">注销账号</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 通知设置 -->
            <div class="settings-box">
                <h4><i class="mdi mdi-bell"></i> 通知设置</h4>
                <hr>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="notify_email" <?php echo $user['notify_email']==1?'checked':'';?>>
                        <label class="custom-control-label" for="notify_email">接收邮件通知</label>
                    </div>
                    <small class="form-text text-muted">包括项目审核、团队申请等重要通知</small>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="notify_update" <?php echo $user['notify_update']==1?'checked':'';?>>
                        <label class="custom-control-label" for="notify_update">接收更新提醒</label>
                    </div>
                    <small class="form-text text-muted">接收网站更新、活动等资讯</small>
                </div>
                <button class="btn btn-primary" onclick="saveNotifySettings()">保存设置</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="../assets/admin/js/jquery.min.js"></script>
<script type="text/javascript" src="../assets/admin/js/bootstrap.min.js"></script>
<script src="../assets/layer/layer.js"></script>
<script>
// 上传头像
$('#avatar-upload').change(function(){
    var file = this.files[0];
    if(file){
        if(!/image\/\w+/.test(file.type)){
            layer.msg('请选择图片文件', {icon: 2});
            return false;
        }
        if(file.size > 2*1024*1024){
            layer.msg('图片大小不能超过2MB', {icon: 2});
            return false;
        }
        var formData = new FormData();
        formData.append('avatar', file);
        var load = layer.load();
        $.ajax({
            url: 'ajax.php?act=uploadAvatar',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(data){
                layer.close(load);
                if(data.code == 1){
                    layer.msg(data.msg, {icon: 1});
                    $('#avatar-preview').attr('src', data.url);
                    setTimeout(function(){
                        location.reload();
                    }, 1000);
                }else{
                    layer.msg(data.msg, {icon: 2});
                }
            },
            error: function(){
                layer.close(load);
                layer.msg('服务器错误', {icon: 2});
            }
        });
    }
});

// 删除头像
function removeAvatar(){
    layer.confirm('确定要删除头像吗？', {
        btn: ['确定','取消']
    }, function(){
        var load = layer.load();
        $.ajax({
            type: 'POST',
            url: 'ajax.php?act=removeAvatar',
            dataType: 'json',
            success: function(data){
                layer.close(load);
                if(data.code == 1){
                    layer.msg(data.msg, {icon: 1});
                    setTimeout(function(){
                        location.reload();
                    }, 1000);
                }else{
                    layer.msg(data.msg, {icon: 2});
                }
            },
            error: function(){
                layer.close(load);
                layer.msg('服务器错误', {icon: 2});
            }
        });
    });
}

// 修改密码
function changePassword(){
    layer.open({
        type: 1,
        title: '修改密码',
        skin: 'layui-layer-rim',
        area: ['400px', '300px'],
        content: '<div class="form-group" style="margin:15px;">'
            +'<input type="password" class="form-control" id="oldpwd" placeholder="请输入原密码">'
            +'<br/><input type="password" class="form-control" id="newpwd" placeholder="请输入新密码">'
            +'<br/><input type="password" class="form-control" id="newpwd2" placeholder="请再次输入新密码">'
            +'<br/><button class="btn btn-primary btn-block" onclick="submitPassword()">确定修改</button>'
            +'</div>'
    });
}

function submitPassword(){
    var oldpwd = $('#oldpwd').val();
    var newpwd = $('#newpwd').val();
    var newpwd2 = $('#newpwd2').val();
    if(oldpwd=='' || newpwd=='' || newpwd2==''){
        layer.msg('请填写完整', {icon: 2});
        return false;
    }
    if(newpwd != newpwd2){
        layer.msg('两次输入的密码不一致', {icon: 2});
        return false;
    }
    var load = layer.load();
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=changePassword',
        data: {oldpwd:oldpwd, newpwd:newpwd},
        dataType: 'json',
        success: function(data){
            layer.close(load);
            if(data.code == 1){
                layer.msg(data.msg, {icon: 1});
                setTimeout(function(){
                    window.location.href = './login.php';
                }, 1000);
            }else{
                layer.msg(data.msg, {icon: 2});
            }
        },
        error: function(){
            layer.close(load);
            layer.msg('服务器错误', {icon: 2});
        }
    });
}

// 修改邮箱
function changeEmail(){
    layer.open({
        type: 1,
        title: '修改邮箱',
        skin: 'layui-layer-rim',
        area: ['400px', '250px'],
        content: '<div class="form-group" style="margin:15px;">'
            +'<input type="password" class="form-control" id="pwd" placeholder="请输入登录密码">'
            +'<br/><input type="email" class="form-control" id="newemail" placeholder="请输入新的邮箱地址">'
            +'<br/><button class="btn btn-primary btn-block" onclick="submitEmail()">确定修改</button>'
            +'</div>'
    });
}

function submitEmail(){
    var pwd = $('#pwd').val();
    var email = $('#newemail').val();
    if(pwd=='' || email==''){
        layer.msg('请填写完整', {icon: 2});
        return false;
    }
    if(!isEmail(email)){
        layer.msg('邮箱格式不正确', {icon: 2});
        return false;
    }
    var load = layer.load();
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=changeEmail',
        data: {pwd:pwd, email:email},
        dataType: 'json',
        success: function(data){
            layer.close(load);
            if(data.code == 1){
                layer.msg(data.msg, {icon: 1});
                setTimeout(function(){
                    location.reload();
                }, 1000);
            }else{
                layer.msg(data.msg, {icon: 2});
            }
        },
        error: function(){
            layer.close(load);
            layer.msg('服务器错误', {icon: 2});
        }
    });
}

// 注销账号
function deleteAccount(){
    layer.confirm('确定要注销账号吗？注销后将无法恢复！', {
        btn: ['确定注销','取消']
    }, function(){
        layer.prompt({
            formType: 1,
            title: '请输入登录密码确认',
        }, function(pwd, index){
            var load = layer.load();
            $.ajax({
                type: 'POST',
                url: 'ajax.php?act=deleteAccount',
                data: {pwd:pwd},
                dataType: 'json',
                success: function(data){
                    layer.close(load);
                    if(data.code == 1){
                        layer.msg(data.msg, {icon: 1});
                        setTimeout(function(){
                            window.location.href = './login.php';
                        }, 1000);
                    }else{
                        layer.msg(data.msg, {icon: 2});
                    }
                },
                error: function(){
                    layer.close(load);
                    layer.msg('服务器错误', {icon: 2});
                }
            });
        });
    });
}

// 保存通知设置
function saveNotifySettings(){
    var notify_email = $('#notify_email').prop('checked')?1:0;
    var notify_update = $('#notify_update').prop('checked')?1:0;
    var load = layer.load();
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=saveNotifySettings',
        data: {notify_email:notify_email, notify_update:notify_update},
        dataType: 'json',
        success: function(data){
            layer.close(load);
            if(data.code == 1){
                layer.msg(data.msg, {icon: 1});
            }else{
                layer.msg(data.msg, {icon: 2});
            }
        },
        error: function(){
            layer.close(load);
            layer.msg('服务器错误', {icon: 2});
        }
    });
}

function isEmail(email){
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function logout(){
    layer.confirm('确定要退出登录吗？', {
        btn: ['确定','取消']
    }, function(){
        var load = layer.load();
        $.ajax({
            type: 'POST',
            url: 'ajax.php?act=logout',
            dataType: 'json',
            success: function(data){
                layer.close(load);
                if(data.code == 1){
                    layer.msg(data.msg, {icon: 1});
                    setTimeout(function(){
                        window.location.href = './login.php';
                    }, 1000);
                }else{
                    layer.msg(data.msg, {icon: 2});
                }
            },
            error: function(){
                layer.close(load);
                layer.msg('服务器错误', {icon: 2});
            }
        });
    });
}
</script>
</body>
</html>