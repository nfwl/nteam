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

// 獲取用戶相關的項目
$projects = $DB->query("SELECT * FROM nteam_project_list WHERE submitter_id=:uid ORDER BY id DESC", [':uid'=>$_SESSION['userid']])->fetchAll();

// 獲取用戶的團隊申請狀態
$team_apply = $DB->getRow("SELECT * FROM nteam_team_member WHERE qq=:qq ORDER BY id DESC LIMIT 1", [':qq'=>$user['qq']]);
?>
<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <title>个人中心 - <?php echo conf('Name');?></title>
    <link rel="icon" href="../favicon.ico" type="image/ico">
    <meta content="<?php echo conf('Descriptison');?>" name="descriptison">
    <meta content="<?php echo conf('Keywords');?>" name="keywords">
    <meta name="author" content="<?php echo conf('Name');?>">
    <link href="../assets/admin/css/bootstrap.min.css" rel="stylesheet">
    <link href="../assets/admin/css/materialdesignicons.min.css" rel="stylesheet">
    <link href="../assets/admin/css/style.min.css" rel="stylesheet">
    <style>
        .profile-box {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,.1);
        }
        .avatar-box {
            text-align: center;
            margin-bottom: 20px;
        }
        .avatar-box img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-bottom: 10px;
        }
        .stats-box {
            text-align: center;
            margin: 20px 0;
        }
        .stats-box .stat-item {
            padding: 10px;
            border-right: 1px solid #eee;
        }
        .stats-box .stat-item:last-child {
            border-right: none;
        }
        .project-item {
            margin-bottom: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row" style="margin-top: 30px;">
        <div class="col-md-3">
            <div class="profile-box">
                <div class="avatar-box">
                    <img src="<?php echo $user['avatar'] ? $user['avatar'] : '../assets/img/default-avatar.jpg'; ?>" alt="头像">
                    <h4><?php echo htmlspecialchars($user['username']); ?></h4>
                </div>
                <div class="stats-box">
                    <div class="row">
                        <div class="col-xs-4 stat-item">
                            <h4><?php echo count($projects); ?></h4>
                            <span>项目</span>
                        </div>
                        <div class="col-xs-4 stat-item">
                            <h4><?php echo $team_apply ? ($team_apply['Audit_status']==1 ? '正式' : '待审') : '未加入'; ?></h4>
                            <span>团队</span>
                        </div>
                        <div class="col-xs-4 stat-item">
                            <h4><?php echo date('Y-m-d', strtotime($user['reg_time'])); ?></h4>
                            <span>注册</span>
                        </div>
                    </div>
                </div>
                <div class="list-group">
                    <a href="profile.php" class="list-group-item active"><i class="mdi mdi-account"></i> 个人资料</a>
                    <a href="projects.php" class="list-group-item"><i class="mdi mdi-folder"></i> 我的项目</a>
                    <a href="settings.php" class="list-group-item"><i class="mdi mdi-settings"></i> 账号设置</a>
                    <a href="javascript:void(0)" onclick="logout()" class="list-group-item text-danger"><i class="mdi mdi-logout"></i> 退出登录</a>
                </div>
            </div>
        </div>
        <div class="col-md-9">
            <div class="profile-box">
                <h4><i class="mdi mdi-account-card-details"></i> 基本资料</h4>
                <hr>
                <form action="javascript:void(0);" method="post" onsubmit="return updateProfile()">
                    <div class="form-group">
                        <label>用户名</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>邮箱</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                        <?php if($user['email_verified'] == 0) { ?>
                        <small class="text-muted">邮箱未验证 <a href="javascript:sendVerifyEmail()">发送验证邮件</a></small>
                        <?php } ?>
                    </div>
                    <div class="form-group">
                        <label>QQ号码</label>
                        <input type="text" class="form-control" name="qq" value="<?php echo htmlspecialchars($user['qq']); ?>" placeholder="请输入QQ号码">
                    </div>
                    <div class="form-group">
                        <label>个人简介</label>
                        <textarea class="form-control" name="bio" rows="3" placeholder="介绍一下自己吧..."><?php echo htmlspecialchars($user['bio']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">保存修改</button>
                </form>
            </div>

            <div class="profile-box">
                <h4><i class="mdi mdi-folder-multiple"></i> 最近的项目</h4>
                <hr>
                <?php if($projects) { foreach($projects as $project) { ?>
                <div class="project-item">
                    <div class="row">
                        <div class="col-md-4">
                            <img src="<?php echo htmlspecialchars($project['img']); ?>" class="img-responsive" alt="项目图片">
                        </div>
                        <div class="col-md-8">
                            <h4><?php echo htmlspecialchars($project['name']); ?></h4>
                            <p><?php echo htmlspecialchars($project['sketch']); ?></p>
                            <div>
                                <span class="label label-<?php echo $project['status']==1 ? 'success' : 'default'; ?>">
                                    <?php echo $project['status']==1 ? '运行中' : '已停止'; ?>
                                </span>
                                <span class="label label-<?php echo $project['Audit_status']==1 ? 'success' : ($project['Audit_status']==2 ? 'danger' : 'warning'); ?>">
                                    <?php echo $project['Audit_status']==1 ? '已审核' : ($project['Audit_status']==2 ? '已拒绝' : '待审核'); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } } else { ?>
                <div class="text-center text-muted">
                    <p>还没有提交过项目</p>
                    <a href="../project.php" class="btn btn-primary">提交项目</a>
                </div>
                <?php } ?>
            </div>

            <?php if(!$team_apply) { ?>
            <div class="profile-box">
                <h4><i class="mdi mdi-account-multiple-plus"></i> 加入团队</h4>
                <hr>
                <p>加入我们的开发团队，与优秀的开发者一起交流学习。</p>
                <a href="../#join" class="btn btn-info">申请加入</a>
            </div>
            <?php } ?>
        </div>
    </div>
</div>

<script type="text/javascript" src="../assets/admin/js/jquery.min.js"></script>
<script type="text/javascript" src="../assets/admin/js/bootstrap.min.js"></script>
<script src="../assets/layer/layer.js"></script>
<script>
function updateProfile(){
    var qq = $("input[name='qq']").val();
    var bio = $("textarea[name='bio']").val();
    
    var load = layer.load();
    $.ajax({
        type : 'POST',
        url : 'ajax.php?act=updateProfile',
        data : {qq:qq,bio:bio},
        dataType : 'json',
        success : function(data) {
            layer.close(load);
            if(data.code == 1){
                layer.msg(data.msg, {icon:1});
                setTimeout(function(){
                    location.reload();
                }, 1000);
            }else{
                layer.msg(data.msg, {icon:2});
            }
        },
        error:function(data){
            layer.close(load);
            layer.msg('服务器错误', {icon:2});
            return false;
        }
    });
    return false;
}

function sendVerifyEmail(){
    var load = layer.load();
    $.ajax({
        type : 'POST',
        url : 'ajax.php?act=sendVerifyEmail',
        dataType : 'json',
        success : function(data) {
            layer.close(load);
            if(data.code == 1){
                layer.msg(data.msg, {icon:1});
            }else{
                layer.msg(data.msg, {icon:2});
            }
        },
        error:function(data){
            layer.close(load);
            layer.msg('服务器错误', {icon:2});
            return false;
        }
    });
}

function logout(){
    layer.confirm('确定要退出登录吗？', {
        btn: ['确定','取消']
    }, function(){
        var load = layer.load();
        $.ajax({
            type : 'POST',
            url : 'ajax.php?act=logout',
            dataType : 'json',
            success : function(data) {
                layer.close(load);
                if(data.code == 1){
                    layer.msg(data.msg, {icon:1});
                    setTimeout(function(){
                        window.location.href = './login.php';
                    }, 1000);
                }else{
                    layer.msg(data.msg, {icon:2});
                }
            },
            error:function(data){
                layer.close(load);
                layer.msg('服务器错误', {icon:2});
                return false;
            }
        });
    });
}
</script>
</body>
</html>