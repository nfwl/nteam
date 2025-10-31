// 處理登入方式切換
$(document).ready(function() {
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

// 處理登入請求
function login(type) {
    var data = {};
    var load = layer.load();

    // 普通用戶名登入
    if(type === 'normal') {
        data = {
            type: 'normal',
            username: $("#normalLoginForm input[name='username']").val(),
            password: $("#normalLoginForm input[name='password']").val()
        };
        if(typeof turnstile !== 'undefined') {
            data.token = turnstile.getResponse();
            if(!data.token) {
                layer.msg('請完成人機驗證', {icon: 2});
                return false;
            }
        }
        handleNormalLogin(data, load);
    }
    // 郵箱登入
    else if(type === 'email') {
        data = {
            type: 'email',
            email: $("#emailLoginForm input[name='email']").val(),
            password: $("#emailLoginForm input[name='password']").val()
        };
        if(typeof turnstile !== 'undefined') {
            data.token = turnstile.getResponse();
            if(!data.token) {
                layer.msg('請完成人機驗證', {icon: 2});
                return false;
            }
        }
        handleNormalLogin(data, load);
    }
    // Linux.do 登入
    else if(type === 'linux') {
        handleLinuxLogin(
            $("#linuxLoginForm input[name='linux_username']").val(),
            $("#linuxLoginForm input[name='linux_password']").val(),
            load
        );
    }
    return false;
}

// 處理普通登入和郵箱登入
function handleNormalLogin(data, load) {
    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=login',
        data: data,
        dataType: 'json',
        success: function(response) {
            layer.close(load);
            if(response.code == 1){
                layer.msg('登入成功', {icon: 1});
                setTimeout(function () {
                    window.location.href = './';
                }, 1000);
            }else{
                layer.msg(response.msg, {icon: 2});
                if(typeof turnstile !== 'undefined') {
                    turnstile.reset();
                }
            }
        },
        error: function() {
            layer.close(load);
            layer.msg('服務器錯誤', {icon: 2});
            if(typeof turnstile !== 'undefined') {
                turnstile.reset();
            }
        }
    });
}

// 處理 Linux.do 登入
function handleLinuxLogin(username, password, load) {
    if(!username || !password) {
        layer.close(load);
        layer.msg('請輸入用戶名和密碼', {icon: 2});
        return;
    }

    $.ajax({
        type: 'POST',
        url: 'https://linux.do/api/auth',
        data: {
            username: username,
            password: password
        },
        dataType: 'json',
        success: function(response) {
            if(response.success){
                // Linux.do 驗證成功，進行本地登入
                $.ajax({
                    type: 'POST',
                    url: 'ajax.php?act=linux_login',
                    data: {
                        username: username,
                        token: response.token
                    },
                    dataType: 'json',
                    success: function(data) {
                        layer.close(load);
                        if(data.code == 1){
                            layer.msg('登入成功', {icon: 1});
                            setTimeout(function () {
                                window.location.href = './';
                            }, 1000);
                        }else{
                            layer.msg(data.msg, {icon: 2});
                        }
                    },
                    error: function() {
                        layer.close(load);
                        layer.msg('本地服務器錯誤', {icon: 2});
                    }
                });
            }else{
                layer.close(load);
                layer.msg('Linux.do驗證失敗', {icon: 2});
            }
        },
        error: function() {
            layer.close(load);
            layer.msg('連接Linux.do失敗', {icon: 2});
        }
    });
}

// 處理註冊請求
function register(type) {
    var data = {};
    var load = layer.load();

    if(type === 'normal') {
        data = {
            type: 'normal',
            username: $("#normalRegForm input[name='username']").val(),
            password: $("#normalRegForm input[name='password']").val(),
            email: $("#normalRegForm input[name='email']").val()
        };
        if(typeof turnstile !== 'undefined') {
            data.token = turnstile.getResponse();
            if(!data.token) {
                layer.msg('請完成人機驗證', {icon: 2});
                return false;
            }
        }
    } else if(type === 'linux') {
        // Linux.do 註冊時直接使用 Linux.do 登入
        handleLinuxLogin(
            $("#linuxRegForm input[name='linux_username']").val(),
            $("#linuxRegForm input[name='linux_password']").val(),
            load
        );
        return false;
    }

    $.ajax({
        type: 'POST',
        url: 'ajax.php?act=register',
        data: data,
        dataType: 'json',
        success: function(response) {
            layer.close(load);
            if(response.code == 1){
                layer.msg('註冊成功，即將跳轉到登入頁面', {icon: 1});
                setTimeout(function () {
                    window.location.href = 'login.php';
                }, 1500);
            }else{
                layer.msg(response.msg, {icon: 2});
                if(typeof turnstile !== 'undefined') {
                    turnstile.reset();
                }
            }
        },
        error: function() {
            layer.close(load);
            layer.msg('服務器錯誤', {icon: 2});
            if(typeof turnstile !== 'undefined') {
                turnstile.reset();
            }
        }
    });
    return false;
}